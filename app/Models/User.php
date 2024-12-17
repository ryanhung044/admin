<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    protected $fillable = [
        'user_code',
        'full_name',
        'email',
        'password',
        'phone_number',
        'address',
        'sex',
        'birthday',
        'citizen_card_number',
        'issue_date',
        'place_of_grant',
        'nation',
        'avatar',
        'role',
        'major_code',
        'narrow_major_code',
        'semester_code',
        'course_code',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

        'password',
        'remember_token',

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed'
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // Định nghĩa mối quan hệ với bảng 'newsletters'
    public function newsletter()
    {
        return $this->hasMany(Newsletter::class, 'user_code', 'user_code');
    }


    public function major()
    {
        return $this->belongsTo(Category::class, 'major_code', 'cate_code');
    }

    public function narrow_major()
    {
        return $this->belongsTo(Category::class, 'narrow_major_code', 'cate_code');
    }
    public function course()
    {
        return $this->belongsTo(Category::class, 'course_code', 'cate_code');
    }


    public function semester()
    {
        return $this->belongsTo(Category::class, 'semester_code', 'cate_code');
    }


    // public function attendance()
    // {
    //     return $this->hasManyThrough(Attendance::class, ClassroomUser::class,  'class_code', 'class_code', 'user_code', 'student_code');
    // }


    public function isAdmin()
    {
        return $this->role === 0;
    }

    public function isTeacher()
    {
        return $this->role === '2';
    }

    public function isStudent()
    {
        return $this->role === '3';
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_user', 'user_code', 'class_code', 'user_code', 'class_code')
            ->withPivot('user_code');
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class, 'schedule_student', 'student_code', 'schedule_id', 'user_code', 'id')
            ->withPivot('student_code');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'student_code', 'user_code');
    }

    public function subjectMajor()
    {
        return $this->belongsToMany(Subject::class, 'categories', 'major_code', 'major_code');
    }

    public function subjectNarrowMajor()
    {
        return $this->belongsToMany(Subject::class, 'categories', 'narrow_major_code', 'major_code');
    }


    // public function subjectMajor()
    // {
    //     return $this->belongsToMany(Subject::class, 'categories', 'cate_code', 'cate_code', 'major_code', 'major_code');
    // }
    // public function subjectNarrowMajor()
    // {
    //     return $this->belongsToMany(Subject::class, 'categories', 'cate_code', 'cate_code', 'narrow_major_code', 'major_code');
    // }
}
