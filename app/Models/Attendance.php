<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected $fillable = [
        'student_code',
        'class_code',
        'date',
        'status',
        'noted'
    ];

    
    // public function user()
    // {
    //     return $this->belongsToThrough(User::class, ClassroomUser::class, 'user_code', 'user_code', 'student_code', 'user_code');
    // }
    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'student_code', 'user_code');
    // }
    
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_code', 'class_code');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'student_code', 'user_code');
    }
    
    public function classroomUser()
    {
        return $this->belongsTo(ClassroomUser::class, 'class_code', 'class_code');
    }
    
    public function schedule()
    {
        return $this->hasOneThrough(
            Schedule::class,
            Classroom::class,
            'class_code', // Foreign key on Classroom
            'class_code', // Foreign key on Schedule
            'class_code', // Local key on Attendance
            'class_code'  // Local key on Classroom
        );
    }

}
