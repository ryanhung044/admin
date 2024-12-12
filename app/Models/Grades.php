<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grades extends Model
{
    use HasFactory;

    protected $fillable = [
        'grades_code',
        'score',
        'user_code',
        'user_grades_code',
        'class_code',
        'subject_code',
        'date'
    ];
}
