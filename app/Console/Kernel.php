<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Lên lịch chạy lệnh mỗi 3,5 tháng (105 ngày)
        $schedule->command('update:exam')->cron('0 2 15 1,5,9 *');
        $schedule->command('update:score')->cron('0 2 15 1,5,9 *');
        $schedule->command('update:semester')->cron('0 2 17 1,5,9 *');

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
