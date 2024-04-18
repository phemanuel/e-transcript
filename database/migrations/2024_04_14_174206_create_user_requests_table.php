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
        Schema::create('user_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('request_id');
            $table->string('email');
            $table->string('matric_no');
            $table->string('phone_no');  
            $table->string('graduation_year');
            $table->string('programme');
            $table->string('clearance_no');
            $table->string('destination_address');
            $table->string('certificate_status');
            $table->string('certificate_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_requests');
    }
};
