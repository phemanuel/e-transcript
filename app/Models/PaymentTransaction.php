<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_id',
        'matric_no',
        'full_name',
        'programme',        
        'email',
        'phone_no',
        'amount',
        'amount_due',
        'transaction_id',
        'transaction_type',
        'transaction_status',
        'transaction_date',
        'response_code',
        'response_status',
        'flicks_transaction_id'
    ];
}
