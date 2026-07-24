<?php

namespace Tests\Feature;

use App\Actions\CreateBusinessAction;
use App\Actions\CreatePostAction;
use App\Actions\UpdateBusinessAction;
use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompositeActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_and_points_roll_back_when_poll_creation_fails(): void
    {
        $user = User::factory()->create(['points' => 0]);
        $category = Category::factory()->create(['type' => 'post']);
        $neighborhood = Neighborhood::factory()->create();

        try {
            (new CreatePostAction)->execute(
                user: $user,
                neighborhood: $neighborhood,
                data: [
                    'category_id' => $category->id,
                    'title' => 'Post que deve falhar',
                    'body' => 'Conteúdo válido para alcançar a criação da enquete.',
                ],
                pollData: [
                    'question' => 'Pergunta válida?',
                    'options' => ['Sim', 'Não'],
                    'ends_at' => 'data inválida',
                ],
            );
        } catch (\Throwable) {
            // Expected failure proves the surrounding transaction.
        }

        $this->assertDatabaseCount('posts', 0);
        $this->assertDatabaseCount('point_events', 0);
        $this->assertSame(0, $user->fresh()->points);
    }

    public function test_invalid_cover_does_not_leave_partial_business_or_update(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);
        $neighborhood = Neighborhood::factory()->create();
        $business = Business::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Nome original',
        ]);
        $invalidImage = UploadedFile::fake()->create('invalid.jpg', 10, 'image/jpeg');
        $data = [
            'category_ids' => [$category->id],
            'name' => 'Nome alterado',
            'neighborhood' => $neighborhood->name,
            'city' => $neighborhood->city,
        ];

        foreach ([
            fn () => (new CreateBusinessAction)->execute($user, $neighborhood, $data, $invalidImage),
            fn () => (new UpdateBusinessAction)->execute($business, $data, $invalidImage),
        ] as $operation) {
            try {
                $operation();
            } catch (\Throwable) {
                // Invalid image must fail before database state changes.
            }
        }

        $this->assertDatabaseCount('businesses', 1);
        $this->assertSame('Nome original', $business->fresh()->name);
        $this->assertDatabaseCount('business_photos', 0);
    }

    public function test_cover_is_replaced_only_after_new_file_is_saved(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::factory()->create(['type' => 'business']);
        $neighborhood = Neighborhood::factory()->create();
        $data = [
            'category_ids' => [$category->id],
            'name' => 'Padaria segura',
            'neighborhood' => $neighborhood->name,
            'city' => $neighborhood->city,
        ];

        $business = (new CreateBusinessAction)->execute(
            $user,
            $neighborhood,
            $data,
            UploadedFile::fake()->image('first.jpg'),
        );
        $oldPath = $business->coverPhoto()->firstOrFail()->path;

        (new UpdateBusinessAction)->execute(
            $business,
            $data,
            UploadedFile::fake()->image('second.png'),
        );

        $newPath = $business->coverPhoto()->firstOrFail()->path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
        $this->assertDatabaseCount('business_photos', 1);
    }
}
