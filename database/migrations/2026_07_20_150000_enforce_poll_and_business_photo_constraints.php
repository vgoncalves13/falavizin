<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->removeDuplicatePolls();
        $this->removeMismatchedPollVotes();
        $this->removeDuplicateBusinessCovers();

        Schema::table('polls', function (Blueprint $table) {
            $table->unique('post_id', 'polls_post_id_unique');
        });

        Schema::table('poll_options', function (Blueprint $table) {
            $table->unique(['poll_id', 'id'], 'poll_options_poll_id_id_unique');
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->foreign(['poll_id', 'poll_option_id'], 'poll_votes_poll_option_belongs_to_poll_foreign')
                ->references(['poll_id', 'id'])
                ->on('poll_options')
                ->cascadeOnDelete();
        });

        $this->dropRollbackSupportIndexes();

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX business_photos_one_cover_per_business_unique
            ON business_photos ((IF(is_cover = 1, business_id, NULL)))
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX business_photos_one_cover_per_business_unique ON business_photos');

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropForeign('poll_votes_poll_option_belongs_to_poll_foreign');
        });

        if (! Schema::hasIndex('poll_votes', 'poll_votes_poll_id_index')) {
            Schema::table('poll_votes', function (Blueprint $table) {
                $table->index('poll_id', 'poll_votes_poll_id_index');
            });
        }

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropIndex('poll_votes_poll_option_belongs_to_poll_foreign');
        });

        if (! Schema::hasIndex('poll_options', 'poll_options_poll_id_index')) {
            Schema::table('poll_options', function (Blueprint $table) {
                $table->index('poll_id', 'poll_options_poll_id_index');
            });
        }

        Schema::table('poll_options', function (Blueprint $table) {
            $table->dropUnique('poll_options_poll_id_id_unique');
        });

        if (! Schema::hasIndex('polls', 'polls_post_id_index')) {
            Schema::table('polls', function (Blueprint $table) {
                $table->index('post_id', 'polls_post_id_index');
            });
        }

        Schema::table('polls', function (Blueprint $table) {
            $table->dropUnique('polls_post_id_unique');
        });
    }

    private function removeDuplicatePolls(): void
    {
        DB::table('polls')
            ->select('post_id')
            ->whereNotNull('post_id')
            ->groupBy('post_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('post_id')
            ->each(function (int $postId): void {
                $duplicateIds = DB::table('polls')
                    ->where('post_id', $postId)
                    ->orderBy('id')
                    ->pluck('id')
                    ->skip(1);

                DB::table('polls')->whereIn('id', $duplicateIds)->delete();
            });
    }

    private function dropRollbackSupportIndexes(): void
    {
        foreach ([
            'poll_votes' => 'poll_votes_poll_id_index',
            'poll_options' => 'poll_options_poll_id_index',
            'polls' => 'polls_post_id_index',
        ] as $table => $index) {
            if (Schema::hasIndex($table, $index)) {
                Schema::table($table, function (Blueprint $blueprint) use ($index): void {
                    $blueprint->dropIndex($index);
                });
            }
        }
    }

    private function removeMismatchedPollVotes(): void
    {
        $voteIds = DB::table('poll_votes')
            ->join('poll_options', 'poll_options.id', '=', 'poll_votes.poll_option_id')
            ->whereColumn('poll_options.poll_id', '!=', 'poll_votes.poll_id')
            ->pluck('poll_votes.id');

        DB::table('poll_votes')->whereIn('id', $voteIds)->delete();
    }

    private function removeDuplicateBusinessCovers(): void
    {
        DB::table('business_photos')
            ->select('business_id')
            ->where('is_cover', true)
            ->groupBy('business_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('business_id')
            ->each(function (int $businessId): void {
                $duplicateIds = DB::table('business_photos')
                    ->where('business_id', $businessId)
                    ->where('is_cover', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->pluck('id')
                    ->skip(1);

                DB::table('business_photos')
                    ->whereIn('id', $duplicateIds)
                    ->update(['is_cover' => false]);
            });
    }
};
