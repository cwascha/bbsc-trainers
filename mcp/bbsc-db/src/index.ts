#!/usr/bin/env node
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  Tool,
} from '@modelcontextprotocol/sdk/types.js';
import mysql from 'mysql2/promise';

// ---------------------------------------------------------------------------
// DB connection — credentials come from env vars set in MCP server config
// ---------------------------------------------------------------------------
const pool = mysql.createPool({
  host:               process.env.BBSC_DB_HOST     ?? 'localhost',
  port:               parseInt(process.env.BBSC_DB_PORT ?? '3306'),
  database:           process.env.BBSC_DB_NAME     ?? '',
  user:               process.env.BBSC_DB_USER     ?? '',
  password:           process.env.BBSC_DB_PASSWORD ?? '',
  waitForConnections: true,
  connectionLimit:    5,
  timezone:           '+00:00',
});

// ---------------------------------------------------------------------------
// Shared helpers
// ---------------------------------------------------------------------------
const MS_PER_DAY    = 86_400_000;
const PAY_ANCHOR    = new Date('2026-01-01T00:00:00Z');
const SPARKS_PREMIUM = 5.00;

function toDateStr(d: Date): string {
  return d.toISOString().split('T')[0];
}

function currentPayPeriod(from: Date = new Date()): { start: string; end: string } {
  const days = Math.floor((from.getTime() - PAY_ANCHOR.getTime()) / MS_PER_DAY);
  const n    = Math.floor(Math.max(days, 0) / 14);
  const s    = new Date(PAY_ANCHOR.getTime() + n * 14 * MS_PER_DAY);
  const e    = new Date(s.getTime() + 13 * MS_PER_DAY);
  return { start: toDateStr(s), end: toDateStr(e) };
}

function sessionHours(start: string, end: string): number {
  const s = new Date(`2000-01-01T${start}`);
  const e = new Date(`2000-01-01T${end}`);
  return Math.abs(e.getTime() - s.getTime()) / 3_600_000;
}

function fmt(n: number): string {
  return `$${n.toFixed(2)}`;
}

// ---------------------------------------------------------------------------
// Payroll calculation — mirrors ReportController::getReport() exactly
// ---------------------------------------------------------------------------
async function calcPayroll(startDate: string, endDate: string) {
  const today = toDateStr(new Date());

  const [trainers]  = await pool.query<any[]>(
    `SELECT id, name, email, phone, pay_rate, venmo, is_lead_trainer
     FROM users WHERE role = 'trainer' ORDER BY name`
  );
  const [avails]    = await pool.query<any[]>(
    `SELECT a.user_id, a.status, a.hours_override,
            td.date, td.program, td.session_start, td.session_end
     FROM availabilities a
     JOIN training_days td ON td.id = a.training_day_id
     WHERE a.status IN ('assigned','confirmed')
       AND td.date BETWEEN ? AND ? AND td.date <= ?`,
    [startDate, endDate, today]
  );
  const [allDays]   = await pool.query<any[]>(
    `SELECT date, program, session_start, session_end
     FROM training_days WHERE date BETWEEN ? AND ? AND date <= ?`,
    [startDate, endDate, today]
  );
  const [overrides] = await pool.query<any[]>(
    `SELECT user_id, hours, planning_hours
     FROM payroll_hours_overrides WHERE period_start = ?`,
    [startDate]
  );
  const [payments]  = await pool.query<any[]>(
    `SELECT user_id, paid_at FROM payroll_payments WHERE period_start = ?`,
    [startDate]
  );
  const [services]  = await pool.query<any[]>(
    `SELECT user_id, description, weekly_amount FROM recurring_services WHERE active = 1`
  );

  const overrideMap = new Map(overrides.map((o: any) => [o.user_id, o]));
  const paidMap     = new Map(payments.map((p: any)  => [p.user_id, p.paid_at]));

  const svcMap = new Map<number, any[]>();
  for (const s of services as any[]) {
    if (!svcMap.has(s.user_id)) svcMap.set(s.user_id, []);
    svcMap.get(s.user_id)!.push(s);
  }

  const availMap = new Map<number, any[]>();
  for (const a of avails as any[]) {
    if (!availMap.has(a.user_id)) availMap.set(a.user_id, []);
    availMap.get(a.user_id)!.push(a);
  }

  // weeks in the period (for recurring services pro-rata)
  const weeks = ((new Date(endDate).getTime() - new Date(startDate).getTime()) / MS_PER_DAY + 1) / 7;

  const results = [];

  for (const trainer of trainers as any[]) {
    const override    = overrideMap.get(trainer.id) as any;
    const svcs        = svcMap.get(trainer.id) ?? [];
    const servicesPay = svcs.reduce((s: number, r: any) => s + r.weekly_amount * weeks, 0);
    const myAvails    = availMap.get(trainer.id) ?? [];

    let sparksHours: number, nonSparksHours: number, planningHours: number;
    let hoursWorked:    number;
    let hoursCalculated: number;
    let isOverridden = false;
    let manuallyAdded = false;

    if (trainer.is_lead_trainer) {
      sparksHours     = (allDays as any[]).filter(d => d.program === 'Sparks')
                          .reduce((s, d) => s + sessionHours(d.session_start, d.session_end), 0);
      nonSparksHours  = (allDays as any[]).filter(d => d.program !== 'Sparks')
                          .reduce((s, d) => s + sessionHours(d.session_start, d.session_end), 0);
      planningHours   = override ? parseFloat(override.planning_hours ?? 0) : 0;
      hoursCalculated = sparksHours + nonSparksHours;
      hoursWorked     = hoursCalculated + planningHours;
    } else {
      sparksHours    = myAvails.filter((a: any) => a.program === 'Sparks')
                         .reduce((s: number, a: any) => s + (a.hours_override ?? sessionHours(a.session_start, a.session_end)), 0);
      nonSparksHours = myAvails.filter((a: any) => a.program !== 'Sparks')
                         .reduce((s: number, a: any) => s + (a.hours_override ?? sessionHours(a.session_start, a.session_end)), 0);
      planningHours   = 0;
      hoursCalculated = sparksHours + nonSparksHours;
      hoursWorked     = override ? parseFloat(override.hours) : hoursCalculated;
      isOverridden    = !!override;
      manuallyAdded   = !!override && hoursCalculated === 0;
    }

    const sparksBonus = trainer.pay_rate ? sparksHours * SPARKS_PREMIUM : 0;
    const basePay     = trainer.pay_rate ? hoursWorked * trainer.pay_rate  : 0;
    const totalPay    = basePay + sparksBonus + servicesPay;
    const sessions    = myAvails.length;

    if (sessions === 0 && !override && svcs.length === 0) continue;

    results.push({
      name:            trainer.name,
      email:           trainer.email,
      venmo:           trainer.venmo ?? null,
      pay_rate:        trainer.pay_rate ? `${fmt(trainer.pay_rate)}/hr` : 'not set',
      is_lead_trainer: !!trainer.is_lead_trainer,
      sessions,
      hours: {
        total:          +hoursWorked.toFixed(2),
        sparks:         +sparksHours.toFixed(2),
        non_sparks:     +nonSparksHours.toFixed(2),
        planning:       +planningHours.toFixed(2),
        calculated:     +hoursCalculated.toFixed(2),
        overridden:     isOverridden && !trainer.is_lead_trainer,
      },
      sparks_bonus:    fmt(sparksBonus),
      services:        svcs.map((s: any) => ({ description: s.description, pay: fmt(s.weekly_amount * weeks) })),
      services_pay:    fmt(servicesPay),
      base_pay:        trainer.pay_rate ? fmt(basePay) : null,
      total_pay:       trainer.pay_rate ? fmt(totalPay) : null,
      paid:            paidMap.has(trainer.id),
      paid_at:         paidMap.has(trainer.id) ? paidMap.get(trainer.id) : null,
      manually_added:  manuallyAdded,
    });
  }

  // Surface service-only trainers not already included
  for (const [userId, svcs] of svcMap) {
    if (results.some(r => r.email === (trainers as any[]).find(t => t.id === userId)?.email)) continue;
    const t = (trainers as any[]).find(t => t.id === userId);
    if (!t) continue;
    const servicesPay = svcs.reduce((s: number, r: any) => s + r.weekly_amount * weeks, 0);
    results.push({
      name: t.name, email: t.email, venmo: t.venmo ?? null,
      pay_rate: t.pay_rate ? `${fmt(t.pay_rate)}/hr` : 'not set',
      is_lead_trainer: !!t.is_lead_trainer, sessions: 0,
      hours: { total: 0, sparks: 0, non_sparks: 0, planning: 0, calculated: 0, overridden: false },
      sparks_bonus: '$0.00',
      services: svcs.map((s: any) => ({ description: s.description, pay: fmt(s.weekly_amount * weeks) })),
      services_pay: fmt(servicesPay),
      base_pay: '$0.00', total_pay: fmt(servicesPay),
      paid: paidMap.has(userId), paid_at: paidMap.get(userId) ?? null,
      manually_added: false,
    });
  }

  results.sort((a, b) => a.name.localeCompare(b.name));
  return { start: startDate, end: endDate, trainers: results };
}

// ---------------------------------------------------------------------------
// Tool handlers
// ---------------------------------------------------------------------------

async function listTrainers() {
  const [rows] = await pool.query<any[]>(
    `SELECT name, email, phone, pay_rate, venmo, is_lead_trainer,
            w9_path, w9_uploaded_at, w9_received_at
     FROM users WHERE role = 'trainer' ORDER BY name`
  );
  return (rows as any[]).map(r => ({
    name:            r.name,
    email:           r.email,
    phone:           r.phone ?? null,
    venmo:           r.venmo ?? null,
    pay_rate:        r.pay_rate ? `${fmt(r.pay_rate)}/hr` : 'not set',
    is_lead_trainer: !!r.is_lead_trainer,
    w9_status:       r.w9_received_at ? 'received'
                   : r.w9_path        ? 'uploaded – pending admin confirmation'
                   : 'missing',
  }));
}

async function missingW9() {
  const [rows] = await pool.query<any[]>(
    `SELECT name, email, phone FROM users
     WHERE role = 'trainer' AND w9_path IS NULL AND w9_received_at IS NULL
     ORDER BY name`
  );
  return { count: (rows as any[]).length, trainers: rows };
}

async function getTrainer(query: string) {
  const [rows] = await pool.query<any[]>(
    `SELECT id, name, email, phone, pay_rate, venmo, is_lead_trainer,
            w9_path, w9_uploaded_at, w9_received_at, created_at
     FROM users WHERE role = 'trainer' AND (name LIKE ? OR email LIKE ?)
     ORDER BY name LIMIT 5`,
    [`%${query}%`, `%${query}%`]
  );
  if (!(rows as any[]).length) return { error: `No trainer found matching "${query}"` };

  const trainer = (rows as any[])[0];
  const period  = currentPayPeriod();

  const [sessions] = await pool.query<any[]>(
    `SELECT a.status, a.hours_override, a.confirmed_at, a.signed_up_at,
            td.date, td.program, td.weekend_number, td.session_start, td.session_end
     FROM availabilities a
     JOIN training_days td ON td.id = a.training_day_id
     WHERE a.user_id = ? ORDER BY td.date DESC LIMIT 40`,
    [trainer.id]
  );

  const payroll = await calcPayroll(period.start, period.end);
  const myPay   = payroll.trainers.find(t => t.email === trainer.email) ?? null;

  return {
    name:            trainer.name,
    email:           trainer.email,
    phone:           trainer.phone ?? null,
    venmo:           trainer.venmo ?? null,
    pay_rate:        trainer.pay_rate ? `${fmt(trainer.pay_rate)}/hr` : 'not set',
    is_lead_trainer: !!trainer.is_lead_trainer,
    w9_status:       trainer.w9_received_at ? 'received'
                   : trainer.w9_path        ? 'uploaded – pending admin confirmation'
                   : 'missing',
    member_since:    new Date(trainer.created_at).toLocaleDateString(),
    current_period:  { start: period.start, end: period.end, payroll: myPay },
    sessions:        (sessions as any[]).map(s => ({
      date:    s.date,
      program: s.program,
      weekend: s.weekend_number,
      status:  s.status,
      hours:   +(s.hours_override ?? sessionHours(s.session_start, s.session_end)).toFixed(2),
    })),
    other_matches: (rows as any[]).length > 1
      ? (rows as any[]).slice(1).map((r: any) => `${r.name} (${r.email})`)
      : undefined,
  };
}

async function getAssignments(weekendNumber?: number, startDate?: string, endDate?: string) {
  let dayRows: any[];
  if (weekendNumber) {
    const [r] = await pool.query<any[]>(
      `SELECT id, date, program, weekend_number, max_spots, session_start, session_end
       FROM training_days WHERE weekend_number = ? ORDER BY date, session_start`,
      [weekendNumber]
    );
    dayRows = r as any[];
  } else if (startDate && endDate) {
    const [r] = await pool.query<any[]>(
      `SELECT id, date, program, weekend_number, max_spots, session_start, session_end
       FROM training_days WHERE date BETWEEN ? AND ? ORDER BY date, session_start`,
      [startDate, endDate]
    );
    dayRows = r as any[];
  } else {
    return { error: 'Provide weekend_number or both start_date and end_date.' };
  }
  if (!dayRows.length) return { error: 'No sessions found.' };

  const ids = dayRows.map(d => d.id);
  const [avails] = await pool.query<any[]>(
    `SELECT a.training_day_id, a.status, u.name, u.phone
     FROM availabilities a JOIN users u ON u.id = a.user_id
     WHERE a.training_day_id IN (${ids.map(() => '?').join(',')})
     ORDER BY u.name`,
    ids
  );

  const aMap = new Map<number, any[]>();
  for (const a of avails as any[]) {
    if (!aMap.has(a.training_day_id)) aMap.set(a.training_day_id, []);
    aMap.get(a.training_day_id)!.push(a);
  }

  return dayRows.map(d => {
    const all      = aMap.get(d.id) ?? [];
    const assigned = all.filter(a => ['assigned', 'confirmed'].includes(a.status));
    const pending  = all.filter(a => a.status === 'pending');
    const hrs      = sessionHours(d.session_start, d.session_end);
    return {
      date:    d.date,
      program: d.program,
      weekend: d.weekend_number,
      time:    `${d.session_start} – ${d.session_end}`,
      hours_per_session: +hrs.toFixed(2),
      spots:   `${assigned.length}/${d.max_spots}`,
      assigned: assigned.map(a => ({ name: a.name, status: a.status })),
      pending:  pending.map(a => a.name),
    };
  });
}

async function getTrainerSessions(query: string, startDate?: string, endDate?: string) {
  const [rows] = await pool.query<any[]>(
    `SELECT id, name, email FROM users WHERE role = 'trainer'
     AND (name LIKE ? OR email LIKE ?) LIMIT 1`,
    [`%${query}%`, `%${query}%`]
  );
  if (!(rows as any[]).length) return { error: `No trainer found matching "${query}"` };
  const trainer = (rows as any[])[0];

  let sql = `SELECT a.status, a.hours_override, a.signed_up_at, a.confirmed_at,
                    td.date, td.program, td.weekend_number, td.session_start, td.session_end
             FROM availabilities a JOIN training_days td ON td.id = a.training_day_id
             WHERE a.user_id = ?`;
  const params: any[] = [trainer.id];
  if (startDate) { sql += ' AND td.date >= ?'; params.push(startDate); }
  if (endDate)   { sql += ' AND td.date <= ?'; params.push(endDate); }
  sql += ' ORDER BY td.date DESC';

  const [sessions] = await pool.query<any[]>(sql, params);
  return {
    trainer: trainer.name,
    email:   trainer.email,
    total:   (sessions as any[]).length,
    sessions: (sessions as any[]).map(s => ({
      date:    s.date,
      program: s.program,
      weekend: s.weekend_number,
      status:  s.status,
      hours:   +(s.hours_override ?? sessionHours(s.session_start, s.session_end)).toFixed(2),
    })),
  };
}

async function unpaidTrainers(startDate: string, endDate: string) {
  const payroll = await calcPayroll(startDate, endDate);
  const unpaid  = payroll.trainers.filter(t => !t.paid && t.total_pay !== null);
  const total   = unpaid.reduce((s, t) => s + parseFloat(t.total_pay!.replace('$', '')), 0);
  return {
    period: `${startDate} – ${endDate}`,
    count:  unpaid.length,
    total_owed: fmt(total),
    trainers: unpaid.map(t => ({ name: t.name, venmo: t.venmo, total_pay: t.total_pay })),
  };
}

// ---------------------------------------------------------------------------
// MCP server setup
// ---------------------------------------------------------------------------

const TOOLS: Tool[] = [
  {
    name: 'list_trainers',
    description: 'List all trainers with name, email, phone, pay rate, Venmo, lead trainer status, and W9 status.',
    inputSchema: { type: 'object', properties: {} },
  },
  {
    name: 'missing_w9',
    description: 'List trainers who have no W9 on file (not uploaded and not marked received).',
    inputSchema: { type: 'object', properties: {} },
  },
  {
    name: 'current_pay_period',
    description: 'Return the start and end dates of the current biweekly pay period.',
    inputSchema: { type: 'object', properties: {} },
  },
  {
    name: 'get_payroll',
    description: 'Full payroll breakdown for a pay period — hours (sparks, non-sparks, planning), Sparks bonus, recurring services, base pay, total pay, and paid status for every trainer with activity.',
    inputSchema: {
      type: 'object',
      properties: {
        start_date: { type: 'string', description: 'Period start YYYY-MM-DD. Defaults to current period.' },
        end_date:   { type: 'string', description: 'Period end YYYY-MM-DD. Defaults to current period.' },
      },
    },
  },
  {
    name: 'get_trainer',
    description: 'Full profile for one trainer: pay rate, W9 status, current period payroll, and recent session history.',
    inputSchema: {
      type: 'object',
      properties: {
        query: { type: 'string', description: 'Trainer name (partial) or email address.' },
      },
      required: ['query'],
    },
  },
  {
    name: 'get_assignments',
    description: 'Show all sessions and their assigned/pending trainers for a weekend number or date range.',
    inputSchema: {
      type: 'object',
      properties: {
        weekend_number: { type: 'number', description: 'Weekend number (1–16).' },
        start_date:     { type: 'string', description: 'Start date YYYY-MM-DD (if no weekend_number).' },
        end_date:       { type: 'string', description: 'End date YYYY-MM-DD (if no weekend_number).' },
      },
    },
  },
  {
    name: 'get_trainer_sessions',
    description: 'Session history for a specific trainer — all statuses, with dates, programs, hours, and confirmation status.',
    inputSchema: {
      type: 'object',
      properties: {
        query:      { type: 'string', description: 'Trainer name (partial) or email.' },
        start_date: { type: 'string', description: 'Optional filter start date YYYY-MM-DD.' },
        end_date:   { type: 'string', description: 'Optional filter end date YYYY-MM-DD.' },
      },
      required: ['query'],
    },
  },
  {
    name: 'unpaid_trainers',
    description: 'List trainers who have earned pay for a period but have not been marked as paid, with total amount owed.',
    inputSchema: {
      type: 'object',
      properties: {
        start_date: { type: 'string', description: 'Period start YYYY-MM-DD. Defaults to current period.' },
        end_date:   { type: 'string', description: 'Period end YYYY-MM-DD. Defaults to current period.' },
      },
    },
  },
];

const server = new Server(
  { name: 'bbsc-db', version: '1.0.0' },
  { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => ({ tools: TOOLS }));

server.setRequestHandler(CallToolRequestSchema, async (req) => {
  const { name, arguments: args = {} } = req.params;
  let result: unknown;

  try {
    const period = currentPayPeriod();
    switch (name) {
      case 'list_trainers':
        result = await listTrainers(); break;
      case 'missing_w9':
        result = await missingW9(); break;
      case 'current_pay_period':
        result = period; break;
      case 'get_payroll':
        result = await calcPayroll(
          (args.start_date as string) ?? period.start,
          (args.end_date   as string) ?? period.end
        ); break;
      case 'get_trainer':
        result = await getTrainer(args.query as string); break;
      case 'get_assignments':
        result = await getAssignments(
          args.weekend_number as number | undefined,
          args.start_date     as string | undefined,
          args.end_date       as string | undefined
        ); break;
      case 'get_trainer_sessions':
        result = await getTrainerSessions(
          args.query      as string,
          args.start_date as string | undefined,
          args.end_date   as string | undefined
        ); break;
      case 'unpaid_trainers':
        result = await unpaidTrainers(
          (args.start_date as string) ?? period.start,
          (args.end_date   as string) ?? period.end
        ); break;
      default:
        result = { error: `Unknown tool: ${name}` };
    }
  } catch (err: any) {
    result = { error: err.message };
  }

  return {
    content: [{ type: 'text', text: JSON.stringify(result, null, 2) }],
  };
});

const transport = new StdioServerTransport();
await server.connect(transport);
