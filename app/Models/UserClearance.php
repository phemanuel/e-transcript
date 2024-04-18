<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserClearance extends Model
{
    use HasFactory;

    protected $fillable = [
        'clearance_no',
        'user_name',
    ];
}
