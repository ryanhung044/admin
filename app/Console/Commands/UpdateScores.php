<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:score';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật điểm cuối môn';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('scores_component')
    ->join('users', 'scores_component.student_code', '=', 'users.user_code')
    ->join('classrooms', 'scores_component.class_code', '=', 'classrooms.class_code')
    ->join('assessment_items', 'scores_component.assessment_code', '=', 'assessment_items.assessment_code')
    ->leftJoin('scores', function ($join) {
        $join->on('scores_component.student_code', '=', 'scores.student_code')
            ->on('classrooms.subject_code', '=', 'scores.subject_code');
    })
    ->selectRaw('
        scores_component.student_code, 
        classrooms.subject_code, 
        classrooms.class_code,
        SUM(scores_component.score * assessment_items.weight) / SUM(assessment_items.weight) AS avg_score,
        MAX(CASE WHEN scores_component.assessment_code = "bvm" THEN scores_component.score ELSE 0 END) AS bvm_score
    ')
    ->groupBy('scores_component.student_code', 'classrooms.subject_code', 'classrooms.class_code')
    ->cursor()
    ->each(function ($row) {
        // Kiểm tra điều kiện avg_score >= 5.0 và điểm của bvm >= 5.0
        $isPass = $row->avg_score >= 5.0 && $row->bvm_score >= 5.0;

        // Cập nhật bảng `scores`
        DB::table('scores')->updateOrInsert(
            [
                'student_code' => $row->student_code,
                'subject_code' => $row->subject_code,
            ],
            [
                'score' => $row->avg_score,
                'is_pass' => $isPass,
                'status' => false,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    });

    
    }
}
