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
        $students = User::where('role', '3')->where('is_active', true)
            ->select('semester_code', 'user_code','id')
            ->get();
        foreach ($students as $student) {
            $currentSemester = DB::table('categories')
                ->where('cate_code', $student->semester_code)
                ->select('value')
                ->first();

            if ($currentSemester) {
                $nextSemester = DB::table('categories')
                    ->where('value', intval($currentSemester->value) + 1) // Chuyển chuỗi thành số nguyên
                    ->select('cate_code')
                    ->first();


                if ($nextSemester) {
                    $student->semester_code = $nextSemester->cate_code;
                    $student->save();
                } else {
                    $hasIncompleteScores = DB::table('scores')
                        ->where('student_code', $student->user_code)
                        ->where('status', false)
                        ->exists();

                    if (!$hasIncompleteScores) {
                        $student->is_active = false;
                        $student->save();
                    }
                }
            }
        }
    }
}
