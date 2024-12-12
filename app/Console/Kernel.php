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
        $schedule->command('update:semester')->cron('0 2 */105 * *'); //chạy lúc 2 giờ sáng, mỗi 105 ngày
        $schedule->command('update:exam')->cron('0 2 */105 * *'); //chạy lúc 2 giờ sáng, mỗi 105 ngày
        $schedule->command('update:score')->cron('0 2 */115 * *'); //chạy lúc 2 giờ sáng, mỗi 105 ngày

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
