<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSemesterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:semester';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật kỳ học cho sinh viên mỗi 3,5 tháng';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $students = User::where('role', '3') // Chỉ lấy sinh viên
            ->select('id', 'semester_code') // Lấy ID và kỳ học hiện tại
            ->get();
        foreach ($students as $student) {
            // Lấy value hiện tại của kỳ học
            $currentSemester = DB::table('categories')
                ->where('cate_code', $student->semester_code)
                ->select('value')
                ->first();

            if ($currentSemester) {
                // Tìm cate_code của kỳ tiếp theo (value + 1)
                $nextSemester = DB::table('categories')
                    ->where('value', $currentSemester->value + 1)
                    ->select('cate_code')
                    ->first();

                if ($nextSemester) {
                    // Cập nhật semester_code của sinh viên
                    $student->semester_code = $nextSemester->cate_code;
                    $student->save(); // Lưu thay đổi
                }
            }
        }
    }
}
