<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'class_code',
        'class_name',
        'exam_score',
        'exam_schedule',
        'description',
        'is_active',
        'subject_code',
        'user_code'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'exam_score' => 'json',
        'study_schedule' => 'json',
        'exam_schedule' => 'json',
        'students' => 'json'
    ];

    // Khai báo tên bảng
    protected $table = 'classrooms';

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_code', 'subject_code');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_code', 'user_code');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'classroom_user', 'class_code', 'user_code', 'class_code', 'user_code');
    }

    public function schedules()
    {

        return $this->hasMany(Schedule::class, 'class_code', 'class_code');
    }
    // Định nghĩa mối quan hệ với bảng 'attendance'
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'class_code', 'class_code');
    }
    // Định nghĩa mối quan hệ với bảng 'scoreComponent'
    public function scorecomponents()
    {
        return $this->hasMany(ScoreComponent::class, 'class_code', 'class_code');
    }
}
