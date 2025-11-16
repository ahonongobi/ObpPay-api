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
        Schema::create('user_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->integer('points');         // The points added (+5, +10...)
            $table->string('reason');          // e.g. "login", "transfer", "deposit", "streak"
            $table->integer('total_score');    // User's score after adding points

            $table->timestamps();
        });

        // Add total_score column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->integer('score')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_scores');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('score');
        });
    }
};
