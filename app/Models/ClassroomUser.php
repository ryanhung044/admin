<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomUser extends Model
{
    use HasFactory;

    protected $table = 'classroom_user';

    protected $fillable = [
        'class_code',
        'user_code',
        'is_modified'
    ];

    
    protected $casts = [
        'is_modified' => 'boolean',
    ];

    public function classroom()
    {
        return $this->hasMany(Classroom::class, 'class_code', 'class_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_code', 'user_code');
    }

//     public function toArray()
// {
//     return [
//         'student_code' => $this->student_code,        
//         'class_code' => $this->class_code,
//         'full_name' => $this->user->full_name ?? null,
//         'class_name' => $this->classroom->class_name ?? null,
//     ];
// }
}
