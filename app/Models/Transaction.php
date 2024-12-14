<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_id',
        'service_id',
        'payment_date',
        'amount_paid',
        'payment_method',
        'type',
        'receipt_number'
    ];
    public function fee()
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }

}
