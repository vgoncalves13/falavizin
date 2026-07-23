<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type');
            $table->string('event_key');
            $table->string('channel');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'notification_type', 'event_key', 'channel'],
                'notification_deliveries_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
