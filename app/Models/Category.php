<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Category extends Model
{
    use SoftDeletes;
    use HasFactory;


    protected $fillable = [
        'cate_code',
        'cate_name',
        'parent_code',
        'value',
        'image',
        'description',
        'type',
        'is_active'
    ];

    public $incrementing = false; // Nếu 'cate_code' không phải là số tự động tăng.
    protected $keyType = 'string'; // Nếu 'cate_code' là chuỗi.
    /**
    * Quan hệ với các danh mục con.
    */
    public function childrens()
    {
        return $this->hasMany(Category::class, 'parent_code', 'cate_code');
    }

    /**
    * Quan hệ với danh mục cha.
    */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_code', 'cate_code');
    }

    // Định nghĩa mối quan hệ với bảng 'newsletters'
    public function newsletter()
    {
        return $this->hasMany(Newsletter::class, 'cate_code', 'cate_code');
    }


    public function semester(){
        return $this->belongsTo(Category::class, 'semester_code', 'cate_code');
    }



    // // Mối quan hệ giữa phòng học và lịch
    // public function roomSchedules(){
    //     return $this->hasMany(Schedule::class, 'room_code', 'cate_code');
    // }
    // // Mối quan hệ giữa ca học và lịch
    // public function sessionSchedules(){
    //     return $this->hasMany(Schedule::class, 'session_code', 'cate_code');
    // }

}
