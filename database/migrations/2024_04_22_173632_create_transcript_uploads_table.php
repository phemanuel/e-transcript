<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transcript_uploads', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('request_id');
            $table->string('email');
            $table->string('transcript_dir');
            $table->string('upload_by');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_uploads');
    }
};
