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
        // password reset code
        Schema::table('users', function (Blueprint $table) {
            $table->string('reset_code')->nullable();
            $table->timestamp('reset_code_expires_at')->nullable();
        });
    }

};