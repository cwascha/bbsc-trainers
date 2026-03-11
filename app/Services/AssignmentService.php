<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\TrainingDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AssignmentService
{
    public function __construct(private readonly SmsService $smsService) {}

    public function assignUpcomingWeekend(): void
    {
        $saturday = Carbon::now()->next(Carbon::SATURDAY);
        $sunday = $saturday->copy()->addDay();

        $satDay = TrainingDay::whereDate('date', $saturday->toDateString())->first();
        $sunDay = TrainingDay::whereDate('date', $sunday->toDateString())->first();

        if ($satDay) {
            $this->assignDay($satDay);
        }
        if ($sunDay) {
            $this->assignDay($sunDay);
        }
    }

    public function assignDay(TrainingDay $day): Collection
    {
        $spotsAvailable = $day->spotsRemaining();
        if ($spotsAvailable <= 0) {
            return collect();
        }

        // Get pending availabilities for this day (not yet assigned)
        $pending = Availability::where('training_day_id', $day->id)
            ->where('status', 'pending')
            ->with('user')
            ->get();

        if ($pending->isEmpty()) {
            return collect();
        }

        // Find trainer IDs already assigned to the OTHER day of this weekend
        $alreadyAssignedThisWeekend = Availability::whereHas('trainingDay', function ($q) use ($day) {
            $q->where('weekend_number', $day->weekend_number)
              ->where('id', '!=', $day->id);
        })
        ->whereIn('status', ['assigned', 'confirmed'])
        ->pluck('user_id')
        ->toArray();

        // Bucket A: no assignment on this weekend yet → higher priority
        $bucketA = $pending->filter(fn($a) => ! in_array($a->user_id, $alreadyAssignedThisWeekend))
            ->sortBy('signed_up_at');

        // Bucket B: already assigned to the other day
        $bucketB = $pending->filter(fn($a) => in_array($a->user_id, $alreadyAssignedThisWeekend))
            ->sortBy('signed_up_at');

        $ordered = $bucketA->values()->merge($bucketB->values());
        $toAssign = $ordered->take($spotsAvailable);

        $assigned = collect();
        foreach ($toAssign as $availability) {
            $availability->update(['status' => 'assigned']);
            $this->smsService->sendAssignmentNotification($availability->user, $day);
            $assigned->push($availability);
        }

        return $assigned;
    }

    public function reassignDay(TrainingDay $day): void
    {
        $spotsAvailable = $day->spotsRemaining();
        if ($spotsAvailable <= 0) {
            return;
        }

        $this->assignDay($day);
    }
}
