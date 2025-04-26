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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('location')->nullable();
            $table->string('type');
            $table->enum('status', ['available', 'sold', 'rented'])->default('available');
            $table->boolean('is_offer')->default(false);
            $table->timestamp('offer_expires_at')->nullable();
            $table->string('currency')->default('USD');
            $table->integer('views')->default(0);
            $table->integer('property_type_id')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('rooms')->nullable();
            $table->enum('seller_type', ['owner', 'agent', 'developer'])->default('owner');
            $table->string('direction')->nullable();
            $table->string('condition')->nullable();
            $table->enum('furnishing', ['furnished', 'unfurnished', 'semi-furnished'])->nullable();
            $table->text('features')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
