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
        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('moderatable'); // moderatable_type + moderatable_id
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // approved, rejected, reported, restored
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('action');
            $table->index('performed_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');
    }
};
