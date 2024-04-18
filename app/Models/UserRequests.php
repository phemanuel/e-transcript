<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRequests extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_id',
        'certificate_name',
        'phone_no',
        'graduation_year',
        'matric_no',
        'programme',
        'clearance_no',
        'destination_address',
        'certificate_status',
        'email',
    ];
}
