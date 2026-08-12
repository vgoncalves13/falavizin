<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Reivindicações pendentes a partir do fluxo legado (claim_user_id).
        $pendingRows = DB::table('businesses')
            ->whereNotNull('claim_user_id')
            ->get(['id', 'claim_user_id', 'claim_requested_at']);

        foreach ($pendingRows as $row) {
            DB::table('business_claims')->insert([
                'business_id' => $row->id,
                'user_id' => $row->claim_user_id,
                'status' => 'pending',
                'message' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
                'created_at' => $row->claim_requested_at ?? $now,
                'updated_at' => $now,
            ]);
        }

        // Vínculos de responsável a partir de negócios já reivindicados.
        $managerRows = DB::table('businesses')
            ->where('claimed', true)
            ->whereNotNull('user_id')
            ->get(['id', 'user_id', 'claimed_at']);

        foreach ($managerRows as $row) {
            DB::table('business_managers')->insert([
                'business_id' => $row->id,
                'user_id' => $row->user_id,
                'role' => 'owner',
                'granted_by' => null,
                'granted_at' => $row->claimed_at ?? $now,
                'revoked_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Não revertemos dados legados; apenas remove vínculos gerados aqui não são distinguíveis.
        // Mantido como no-op seguro: negócios continuam com seus valores originais.
    }
};
