<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferScheduleTimeframe extends Model
{
    use HasFactory;
    
    protected $table = 'transfer_schedule_timeframe';
    protected $fillable = [
        'start_time', 
        'end_time'
    ];


}
