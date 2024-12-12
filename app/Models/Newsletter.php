<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'tags',
        'content',
        'image',
        'description',
        'type',
        'order',
        'expiry_date',
        'is_active',
        'notification_object',
        'user_code',
        'cate_code'
    ];

    protected $casts = [
        'tags' => 'json',
        'notification_object' => 'json'
    ];

    // Định nghĩa mối quan hệ với bảng 'categories'
    public function category()
    {
        return $this->belongsTo(Category::class, 'cate_code', 'cate_code');
    }

    // Định nghĩa mối quan hệ với bảng 'users'
    public function user()
    {
        return $this->belongsTo(User::class, 'user_code', 'user_code');
    }
}
