<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoCheckoutAbsentees extends Command
{
    protected $signature   = 'attendance:auto-checkout {--before= : Process records before this date (Y-m-d), defaults to yesterday}';
    protected $description = 'Auto-checkout users who checked in but never checked out for past days';

    public function handle(): int
    {
        $before = $this->option('before')
            ? Carbon::parse($this->option('before'))->toDateString()
            : Carbon::yesterday()->toDateString();

        $records = Attendance::whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->whereDate('date', '<=', $before)
            ->get();

        if ($records->isEmpty()) {
            $this->info('No open check-in sessions found.');
            return self::SUCCESS;
        }

        foreach ($records as $record) {
            $endOfDay = Carbon::parse($record->date)->endOfDay();
            $record->update(['check_out_at' => $endOfDay]);
        }

        $this->info("Auto-checked out {$records->count()} record(s).");

        return self::SUCCESS;
    }
}
