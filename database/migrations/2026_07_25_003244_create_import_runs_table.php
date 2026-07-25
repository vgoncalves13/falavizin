<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('neighborhood_id');
            $table->string('status')->default('pending');
            $table->string('mode')->default('complete');
            $table->json('config');
            $table->json('stats')->nullable();
            $table->json('cells')->nullable();
            $table->json('seen_place_ids')->nullable();
            $table->integer('requests_made')->default(0);
            $table->integer('requests_budget')->default(200);
            $table->string('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('neighborhood_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_runs');
    }
};
