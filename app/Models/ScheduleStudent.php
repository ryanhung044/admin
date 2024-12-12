<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleStudent extends Model
{
    use HasFactory;

    protected $table = 'schedule_student';

    public function schedule(){
        return $this->belongsTo(Schedule::class);
    }
}
