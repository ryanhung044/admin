<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_code',
        'service_name',
        'content',
        'status',
        'reason',
        'amount',
        'file_path'
    ];

    public function student(){
        return $this->belongsTo(User::class , 'user_code' , 'user_code');
    }
}
