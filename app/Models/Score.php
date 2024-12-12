<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_code', 
        'subject_code',
        'score',
        'is_pass',
        'status',
    ];
    public function Subject() {
        return $this->belongsTo(Subject::class, 'subject_code', 'subject_code');
    }
}
