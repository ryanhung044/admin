<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    // môn học
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'subject_code',
        'subject_name',
        'tuition',
        'credit_number',
        're_study_fee',
        'total_sessions',
        'assessments',
        'exam_day',
        'description',
        'image',
        'is_active',
        'semester_code',
        'major_code',
        'narrow_major_code',
    ];
    protected $casts = [
        'assessments' => 'array',
    ];

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'subject_code', 'subject_code');
    }

    public function major()
    {
        return $this->belongsTo(Category::class, 'major_code', 'cate_code');
    }

    public function semester()
    {
        return $this->belongsTo(Category::class, 'semester_code', 'cate_code');
    }

    public function subjectAssessment()
    {
        return $this->belongsToMany(AssessmentItem::class, 'subject_assessment', 'subject_code', 'assessment_code', 'subject_code', 'assessment_code');
    }
}
