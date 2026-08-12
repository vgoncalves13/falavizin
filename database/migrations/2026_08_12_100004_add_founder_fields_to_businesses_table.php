<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('is_founder')->default(false)->after('reported_reason');
            $table->timestamp('founder_granted_at')->nullable()->after('is_founder');

            $table->index('is_founder');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex(['is_founder']);
            $table->dropColumn(['founder_granted_at', 'is_founder']);
        });
    }
};
