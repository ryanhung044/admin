<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:exam';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật điểm tự động';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //     DB::table('scores_component')
        //         ->join('users', 'scores_component.student_code', '=', 'users.user_code')
        //         ->join('classrooms', 'scores_component.class_code', '=', 'classrooms.class_code')
        //         ->join('assessment_items', 'scores_component.assessment_code', '=', 'assessment_items.assessment_code')
        //         ->leftJoin('scores', function ($join) {
        //             $join->on('scores_component.student_code', '=', 'scores.student_code')
        //                 ->on('classrooms.subject_code', '=', 'scores.subject_code');
        //         })
        //         ->selectRaw('
        //     scores_component.student_code, 
        //     classrooms.subject_code, 
        //     classrooms.class_code,
        //     SUM(scores_component.score * assessment_items.weight) / SUM(assessment_items.weight) AS avg_score 
        // ')
        //         ->groupBy('scores_component.student_code', 'classrooms.subject_code', 'classrooms.class_code')
        //         ->cursor()
        //         ->each(function ($row) {
        //             // Cập nhật bảng `scores`
        //             DB::table('scores')->updateOrInsert(
        //                 [
        //                     'student_code' => $row->student_code,
        //                     'subject_code' => $row->subject_code,
        //                 ],
        //                 // [
        //                 //     'score' => $row->avg_score,
        //                 //     'is_pass' => $row->avg_score >= 5.0,
        //                 //     'status' => false,
        //                 //     'updated_at' => now(),
        //                 //     'created_at' => now(),
        //                 // ]
        //             );

        //             // Kiểm tra và cập nhật bảng `classroom_user`
        //             DB::table('classroom_user')
        //                 ->where('class_code', $row->class_code)
        //                 ->where('user_code', $row->student_code)
        //                 ->update([
        //                     'is_qualified' => $row->avg_score >= 5.0,
        //                     'updated_at' => now(),
        //                 ]);
        //         });

        DB::table('scores_component')
            ->join('users', 'scores_component.student_code', '=', 'users.user_code')
            ->join('classrooms', 'scores_component.class_code', '=', 'classrooms.class_code')
            ->join('assessment_items', 'scores_component.assessment_code', '=', 'assessment_items.assessment_code')
            ->leftJoin('scores', function ($join) {
                $join->on('scores_component.student_code', '=', 'scores.student_code')
                    ->on('classrooms.subject_code', '=', 'scores.subject_code');
            })
            ->leftJoin('attendances', function ($join) {
                $join->on('scores_component.student_code', '=', 'attendances.student_code')
                    ->on('scores_component.class_code', '=', 'attendances.class_code');
            })
            ->selectRaw('
                scores_component.student_code, 
                classrooms.subject_code, 
                classrooms.class_code,
                SUM(scores_component.score * assessment_items.weight) / SUM(assessment_items.weight) AS avg_score,
                COUNT(attendances.id) AS total_sessions,
                SUM(CASE WHEN attendances.status = "absent" THEN 1 ELSE 0 END) AS total_absent
            ')
            ->groupBy('scores_component.student_code', 'classrooms.subject_code', 'classrooms.class_code')
            ->cursor()
            ->each(function ($row) {
                // $this->info('Cập nhật điểm và trạng thái học sinh thành công!', $row);

                // Tính tỷ lệ vắng mặt
                $absenceRate = $row->total_sessions > 0
                    ? ($row->total_absent / $row->total_sessions) * 100
                    : 0;

                // Kiểm tra điều kiện is_qualified
                $isQualified = !($absenceRate >= 20 || $row->avg_score < 4.0);

                // Cập nhật bảng `classroom_user`
                DB::table('classroom_user')
                    ->where('class_code', $row->class_code)
                    ->where('user_code', $row->student_code)
                    ->update([
                        'is_qualified' => $isQualified,
                        'updated_at' => now(),
                    ]);
            });

        // In ra thông báo sau khi hoàn thành

    }
}
