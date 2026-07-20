<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_events', function (Blueprint $table): void {
            $table->string('idempotency_key')->nullable()->after('reason');
        });

        $seen = [];
        $discardedIds = [];

        DB::table('point_events')
            ->whereNotNull('pointable_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $event) use (&$seen, &$discardedIds): void {
                if ($event->pointable_type === 'App\\Models\\Vote') {
                    $vote = DB::table('votes')->find($event->pointable_id);

                    if (! $vote) {
                        $discardedIds[] = $event->id;

                        return;
                    }

                    $key = implode(':', [
                        $event->reason,
                        $vote->votable_type,
                        $vote->votable_id,
                        'voter',
                        $vote->user_id,
                    ]);
                } else {
                    $key = implode(':', [
                        $event->reason,
                        $event->pointable_type,
                        $event->pointable_id,
                    ]);
                }

                if (isset($seen[$key])) {
                    $discardedIds[] = $event->id;

                    return;
                }

                $seen[$key] = true;
                DB::table('point_events')->where('id', $event->id)->update(['idempotency_key' => $key]);
            });

        if ($discardedIds !== []) {
            DB::table('point_events')->whereIn('id', $discardedIds)->delete();
        }

        DB::table('users')->update(['points' => 0]);
        DB::table('point_events')
            ->selectRaw('user_id, SUM(points) AS total')
            ->groupBy('user_id')
            ->get()
            ->each(fn (object $total) => DB::table('users')
                ->where('id', $total->user_id)
                ->update(['points' => $total->total]));

        Schema::table('point_events', function (Blueprint $table): void {
            $table->unique('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('point_events', function (Blueprint $table): void {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
