<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\TrainingDay;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    // ── Seasons ──────────────────────────────────────────────────────────────

    public function index()
    {
        $seasons = Season::with('trainingDays')
            ->orderByDesc('year')
            ->orderBy('name')
            ->get();

        return view('admin.seasons.index', compact('seasons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'year' => 'required|integer|min:2020|max:2040',
            'status' => 'required|in:upcoming,active,completed',
        ]);

        Season::create([
            'name'   => $request->name,
            'year'   => $request->year,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.seasons.index')->with('success', "Season \"{$request->name}\" created.");
    }

    public function update(Request $request, Season $season): RedirectResponse
    {
        $request->validate([
            'name'   => 'required|string|max:100',
            'year'   => 'required|integer|min:2020|max:2040',
            'status' => 'required|in:upcoming,active,completed',
        ]);

        $season->update($request->only('name', 'year', 'status'));

        return redirect()->route('admin.seasons.show', $season)->with('success', 'Season updated.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        $season->delete();

        return redirect()->route('admin.seasons.index')->with('success', 'Season deleted.');
    }

    public function show(Season $season)
    {
        $season->load('trainingDays');

        return view('admin.seasons.show', compact('season'));
    }

    // ── Training Days ────────────────────────────────────────────────────────

    public function storeDay(Request $request, Season $season): RedirectResponse
    {
        $request->validate([
            'date'          => 'required|date',
            'program'       => 'nullable|string|max:100',
            'weekend_number'=> 'required|integer|min:1|max:99',
            'session_start' => 'required|date_format:H:i',
            'session_end'   => 'required|date_format:H:i|after:session_start',
            'max_spots'     => 'required|integer|min:1|max:99',
        ]);

        TrainingDay::create([
            'season_id'      => $season->id,
            'date'           => $request->date,
            'program'        => $request->program ?: null,
            'weekend_number' => $request->weekend_number,
            'session_start'  => $request->session_start . ':00',
            'session_end'    => $request->session_end . ':00',
            'max_spots'      => $request->max_spots,
        ]);

        return back()->with('success', 'Training day added.');
    }

    public function updateDay(Request $request, Season $season, TrainingDay $day): RedirectResponse
    {
        $request->validate([
            'date'          => 'required|date',
            'program'       => 'nullable|string|max:100',
            'weekend_number'=> 'required|integer|min:1|max:99',
            'session_start' => 'required|date_format:H:i',
            'session_end'   => 'required|date_format:H:i|after:session_start',
            'max_spots'     => 'required|integer|min:1|max:99',
        ]);

        $day->update([
            'date'           => $request->date,
            'program'        => $request->program ?: null,
            'weekend_number' => $request->weekend_number,
            'session_start'  => $request->session_start . ':00',
            'session_end'    => $request->session_end . ':00',
            'max_spots'      => $request->max_spots,
        ]);

        return back()->with('success', 'Training day updated.');
    }

    public function destroyDay(Season $season, TrainingDay $day): RedirectResponse
    {
        $day->delete();

        return back()->with('success', 'Training day removed.');
    }

    // ── Bulk generate training days for a season ─────────────────────────────

    public function generateDays(Request $request, Season $season): RedirectResponse
    {
        $request->validate([
            'start_date'    => 'required|date',
            'num_weekends'  => 'required|integer|min:1|max:52',
            'skip_dates'    => 'nullable|string',
            'sat_start'     => 'required|date_format:H:i',
            'sat_end'       => 'required|date_format:H:i|after:sat_start',
            'sat_spots'     => 'required|integer|min:1|max:99',
            'sat_program'   => 'nullable|string|max:100',
            'sun_start'     => 'nullable|date_format:H:i',
            'sun_end'       => 'nullable|date_format:H:i|after:sun_start',
            'sun_spots'     => 'nullable|integer|min:1|max:99',
            'sun_program'   => 'nullable|string|max:100',
            'include_sunday'=> 'nullable|boolean',
        ]);

        // Parse skip dates
        $skipDates = collect(explode(',', $request->skip_dates ?? ''))
            ->map(fn($d) => trim($d))
            ->filter(fn($d) => strtotime($d))
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $start    = Carbon::parse($request->start_date);
        $count    = 0;
        $weekend  = $season->trainingDays()->max('weekend_number') ?? 0;

        // Find the first Saturday on or after start_date
        if ($start->dayOfWeek !== Carbon::SATURDAY) {
            $start->next(Carbon::SATURDAY);
        }

        for ($w = 0; $w < $request->num_weekends; $w++) {
            $saturday = $start->copy()->addWeeks($w);
            $sunday   = $saturday->copy()->addDay();

            if (in_array($saturday->toDateString(), $skipDates) && in_array($sunday->toDateString(), $skipDates)) {
                continue;
            }

            $weekend++;

            // Saturday
            if (!in_array($saturday->toDateString(), $skipDates)) {
                TrainingDay::firstOrCreate(
                    ['date' => $saturday->toDateString(), 'program' => $request->sat_program ?: null],
                    [
                        'season_id'      => $season->id,
                        'weekend_number' => $weekend,
                        'session_start'  => $request->sat_start . ':00',
                        'session_end'    => $request->sat_end . ':00',
                        'max_spots'      => $request->sat_spots,
                    ]
                );
                $count++;
            }

            // Sunday (optional)
            if ($request->include_sunday && $request->sun_start && $request->sun_end) {
                if (!in_array($sunday->toDateString(), $skipDates)) {
                    TrainingDay::firstOrCreate(
                        ['date' => $sunday->toDateString(), 'program' => $request->sun_program ?: null],
                        [
                            'season_id'      => $season->id,
                            'weekend_number' => $weekend,
                            'session_start'  => $request->sun_start . ':00',
                            'session_end'    => $request->sun_end . ':00',
                            'max_spots'      => $request->sun_spots ?? $request->sat_spots,
                        ]
                    );
                    $count++;
                }
            }
        }

        return back()->with('success', "{$count} training days generated for \"{$season->name}\".");
    }
}
