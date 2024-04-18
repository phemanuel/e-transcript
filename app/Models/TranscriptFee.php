<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranscriptFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_amount',
    ];
}
