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
       
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('user_id');
                $table->decimal('amount', 12, 2);
                $table->decimal('fees', 12, 2)->default(0);

                $table->string('method');    // mtn, moov, wave, bank, etc.
                $table->string('recipient'); // numéro mobile ou IBAN

                $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])
                    ->default('pending');

                $table->text('admin_notes')->nullable();

                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
