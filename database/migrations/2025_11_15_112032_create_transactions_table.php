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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // deposit, withdraw, transfer_in, transfer_out, purchase...
            $table->decimal('amount', 15, 2);
            $table->string('currency', 10)->default('XOF');
            $table->string('description')->nullable();
            $table->string('status')->default('completed'); // pending, failed...
            $table->json('meta')->nullable(); // ex: { "from": "...", "to": "..." }
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
