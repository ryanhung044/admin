<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'room_code',
        'class_code',
        'session_code',
        'teacher_code',
        'type',
    ];

    public function classroom(){
        return $this->belongsTo(Classroom::class, 'class_code', 'class_code');
    }

    public function room(){
        return $this->belongsTo(Category::class, 'room_code', 'cate_code');
    }

    public function session(){
        return $this->belongsTo(Category::class, 'session_code', 'cate_code');
    }

    public function teacher(){
        return $this->belongsTo(User::class, 'teacher_code', 'user_code');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'classroom_user', 'class_code', 'user_code', 'class_code', 'user_code');
    }



    // public function toArray()
    // {
    //     return [
    //         'class_code' => $this->class_code,
    //         'date' => $this->date,
    //         'classroom' => [
    //             'class_name' => $this->classroom->class_name ?? null,
    //         ],
    //         'room' => [
    //             'cate_name' => $this->room->cate_name ?? null,
    //             'value' => $this->room->value ?? null,
    //         ],
    //         'session' => [
    //             'cate_code' => $this->session->cate_code ?? null,
    //             'cate_name' => $this->session->cate_name ?? null,
    //             'value' => $this->session->value ?? null,

    //         ],
    //         'teacher'=> [
    //             'user_code'=> $this->teacher->user_code ?? null
    //         ]
    //     ];
    // }

    // Các sinh viên trong buổi thi
    public function students(){
        return $this->belongsToMany(User::class,  'schedule_student','schedule_id', 'student_code', 'id', 'user_code');
    }
}
