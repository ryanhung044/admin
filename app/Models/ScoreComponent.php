<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoreComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_code',
        'class_code',
        'score',
        'assessment_code'
    ];
    protected $table = 'scores_component';

    public function user() {
        return $this->belongsTo(User::class, 'student_code', 'user_code');
    }

    public function classroom() {
        return $this->belongsTo(Classroom::class, 'class_code', 'class_code');
    }

    public function assessmentItem() {
        return $this->belongsTo(AssessmentItem::class, 'assessment_code', 'assessment_code');
    }
}
