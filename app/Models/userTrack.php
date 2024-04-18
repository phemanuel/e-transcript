<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class userTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_id',
        'certificate_status',
        'approved_by',
        'comments',
    ];
}
