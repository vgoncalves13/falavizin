<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_onboarding_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('step'); // via BusinessOnboardingStep enum
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'step']);
            $table->index('step');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_onboarding_steps');
    }
};
