<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_code',
        'name',
        'weight'
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_assessment');
    }
}
