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
        Schema::table('passcodes', function (Blueprint $table) {
            $table->boolean('has_joined')->nullable();
            $table->dateTime('date_scheduled')->nullable();
            $table->dateTime('date_time_joined')->nullable();
            $table->dateTime('date_time_left')->nullable();
            $table->text('logs')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
