<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedLogins extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'email',
    ];
}
