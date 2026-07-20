<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropColumn('claim_token');
            $table->foreignId('claim_user_id')->nullable()->after('claimed')->constrained('users')->nullOnDelete();
            $table->timestamp('claim_requested_at')->nullable()->after('claim_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('claim_user_id');
            $table->dropColumn('claim_requested_at');
            $table->string('claim_token')->nullable()->after('claimed');
        });
    }
};
