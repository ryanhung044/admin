<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferScheduleHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_code',
        'from_class_code',
        'to_class_code',
    ];
}
