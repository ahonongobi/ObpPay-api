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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();

            $table->text('description')->nullable();

            $table->decimal('price', 12, 2)->default(0); // 25_000_000.00 OK
            $table->string('currency')->default('XOF'); // utile si un jour multi-devise

            $table->string('image')->nullable(); // URL ou path du storage

            $table->integer('stock')->default(0); // optionnel mais utile

            $table->json('tags')->nullable(); // ex: ["bio", "local", "promo"]

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
