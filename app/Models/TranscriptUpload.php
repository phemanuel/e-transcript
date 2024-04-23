<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranscriptUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'transcript_dir',
        'upload_by',
        'request_id',
        'status',
    ];
}
