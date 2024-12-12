<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_code',
        'id',
        'total_amount',
        'semester_code',
        'amount',
        'start_date',
        'due_date',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_code', 'user_code');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'fee_id');
    }
}
