<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoCheckoutAbsentees extends Command
{
    protected $signature   = 'attendance:auto-checkout';
    protected $description = 'Auto-checkout users who checked in but never checked out for past days';

    public function handle(): int
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $records = Attendance::whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->whereDate('date', '<=', $yesterday)
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
