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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); //ممكن يكون المالك مستخدم او وسيط في المسقبل
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            // $table->string('ad_counter')->default('2');

            $table->unsignedBigInteger('followers_count')->default(0);
            $table->unsignedBigInteger('views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
