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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->constrained();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood');
            $table->string('city')->default('');
            $table->json('opening_hours')->nullable();
            $table->string('website')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('google_place_id')->nullable()->unique();
            $table->string('plan')->default('free'); // 'free' | 'featured' — via BusinessPlan enum
            $table->string('status')->default('approved'); // 'pending' | 'approved' | 'rejected' — via BusinessStatus enum
            $table->boolean('claimed')->default(false);
            $table->string('claim_token')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['neighborhood', 'status']);
            $table->index(['plan', 'status']);
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
