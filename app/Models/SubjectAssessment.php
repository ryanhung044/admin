<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_code',
        'assessment_code'
    ];
    protected $table = 'subject_assessment';

    public function subject() {
        return $this->beLongsTo(Subject::class, 'subject_code', 'subject_code');
    }

    public function assessment() {
        return $this->beLongsTo(AssessmentItem::class, 'assessment_code', 'assessment_code');
    }
}
