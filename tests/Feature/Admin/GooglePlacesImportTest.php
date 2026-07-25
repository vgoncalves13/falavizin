<?php

namespace Tests\Feature\Admin;

use App\Enums\BusinessStatus;
use App\Jobs\EnrichBusinessFromGoogle;
use App\Livewire\Admin\GooglePlacesImport;
use App\Models\Business;
use App\Models\Category;
use App\Models\Neighborhood;
use App\Models\User;
use App\Services\GooglePlacesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

class GooglePlacesImportTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function makeCategory(): Category
    {
        return Category::factory()->create(['type' => 'business']);
    }

    private function makeNeighborhood(array $attributes = []): Neighborhood
    {
        return Neighborhood::factory()->create($attributes);
    }

    private function fakePlaces(): Collection
    {
        return collect([
            [
                'place_id' => 'ChIJ_place_001',
                'name' => 'Padaria do João',
                'address' => 'Rua das Flores, 100 — Copacabana',
                'lat' => -22.9711,
                'lng' => -43.1823,
                'phone' => '(21) 99999-1111',
                'website' => null,
                'types' => ['bakery'],
                'already_imported' => false,
            ],
            [
                'place_id' => 'ChIJ_place_002',
                'name' => 'Farmácia Central',
                'address' => 'Av. Principal, 200 — Copacabana',
                'lat' => -22.9720,
                'lng' => -43.1830,
                'phone' => null,
                'website' => 'https://farmacentral.com',
                'types' => ['pharmacy'],
                'already_imported' => false,
            ],
        ]);
    }

    public function test_admin_can_access_the_import_page(): void
    {
        $this->makeCategory();

        $response = $this->actingAs($this->makeAdmin())
            ->get(route('admin.google-places-import'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_the_import_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->get(route('admin.google-places-import'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_from_import_page(): void
    {
        $response = $this->get(route('admin.google-places-import'));

        $response->assertRedirect(route('login'));
    }

    public function test_search_returns_results_from_service(): void
    {
        $admin = $this->makeAdmin();
        $category = $this->makeCategory();
        $neighborhood = $this->makeNeighborhood();

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearbySimple')
                ->once()
                ->andReturn($this->fakePlaces());
        });

        Livewire::actingAs($admin)
            ->test(GooglePlacesImport::class)
            ->set('neighborhoodId', $neighborhood->id)
            ->set('categoryId', $category->id)
            ->set('lat', -22.9711)
            ->set('lng', -43.1823)
            ->set('radius', 1000)
            ->call('search')
            ->assertSet('searched', true)
            ->assertCount('results', 2);
    }

    public function test_search_marks_already_imported_places(): void
    {
        $admin = $this->makeAdmin();
        $category = $this->makeCategory();
        $neighborhood = $this->makeNeighborhood();

        Business::factory()->create([
            'google_place_id' => 'ChIJ_place_001',
            'category_id' => $category->id,
        ]);

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearbySimple')
                ->once()
                ->andReturn($this->fakePlaces());
        });

        $component = Livewire::actingAs($admin)
            ->test(GooglePlacesImport::class)
            ->set('neighborhoodId', $neighborhood->id)
            ->set('categoryId', $category->id)
            ->call('search');

        $results = $component->get('results');
        $place1 = collect($results)->firstWhere('place_id', 'ChIJ_place_001');
        $place2 = collect($results)->firstWhere('place_id', 'ChIJ_place_002');

        $this->assertTrue($place1['already_imported']);
        $this->assertFalse($place2['already_imported']);
    }

    public function test_import_creates_businesses_with_neighborhood(): void
    {
        Queue::fake();

        $admin = $this->makeAdmin();
        $category = $this->makeCategory();
        $neighborhood = $this->makeNeighborhood([
            'name' => 'Copacabana',
            'city' => 'Rio de Janeiro',
        ]);

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearbySimple')
                ->once()
                ->andReturn($this->fakePlaces());
        });

        Livewire::actingAs($admin)
            ->test(GooglePlacesImport::class)
            ->set('neighborhoodId', $neighborhood->id)
            ->set('categoryId', $category->id)
            ->call('search')
            ->set('selected', ['ChIJ_place_001', 'ChIJ_place_002'])
            ->call('import');

        $this->assertDatabaseHas('businesses', [
            'google_place_id' => 'ChIJ_place_001',
            'name' => 'Padaria do João',
            'neighborhood_id' => $neighborhood->id,
            'neighborhood' => 'Copacabana',
            'city' => 'Rio de Janeiro',
            'status' => BusinessStatus::Approved->value,
            'claimed' => false,
            'user_id' => null,
        ]);

        $this->assertDatabaseHas('businesses', [
            'google_place_id' => 'ChIJ_place_002',
            'name' => 'Farmácia Central',
            'neighborhood_id' => $neighborhood->id,
            'status' => BusinessStatus::Approved->value,
        ]);

        $this->assertDatabaseCount('businesses', 2);
        $this->assertDatabaseCount('business_categories', 2);
        $this->assertSame(
            ['(21) 99999-1111'],
            Business::where('google_place_id', 'ChIJ_place_001')->firstOrFail()->phone,
        );
        Queue::assertPushed(EnrichBusinessFromGoogle::class, 2);
        Queue::assertPushed(EnrichBusinessFromGoogle::class, function (EnrichBusinessFromGoogle $job): bool {
            $secondBusiness = Business::where('google_place_id', 'ChIJ_place_002')->firstOrFail();

            return $job->businessId === $secondBusiness->id
                && $job->delay?->isAfter(now()->addSecond());
        });
    }

    public function test_import_does_not_duplicate_existing_businesses(): void
    {
        Queue::fake();

        $admin = $this->makeAdmin();
        $category = $this->makeCategory();
        $neighborhood = $this->makeNeighborhood();

        Business::factory()->create([
            'google_place_id' => 'ChIJ_place_001',
            'category_id' => $category->id,
        ]);

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearbySimple')
                ->once()
                ->andReturn($this->fakePlaces());
        });

        Livewire::actingAs($admin)
            ->test(GooglePlacesImport::class)
            ->set('neighborhoodId', $neighborhood->id)
            ->set('categoryId', $category->id)
            ->call('search')
            ->set('selected', ['ChIJ_place_001'])
            ->call('import');

        // Should still be only 1 (the pre-existing one — no new import)
        $this->assertDatabaseCount('businesses', 1);
    }

    public function test_import_matches_google_type_and_uses_outros_as_fallback(): void
    {
        Queue::fake();

        $admin = $this->makeAdmin();
        $neighborhood = $this->makeNeighborhood();
        $food = Category::factory()->create(['slug' => 'alimentacao', 'type' => 'both']);
        $other = Category::where('slug', 'outros')->firstOrFail();

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearbySimple')
                ->once()
                ->andReturn($this->fakePlaces()->push([
                    'place_id' => 'ChIJ_place_unknown',
                    'name' => 'Local sem correspondência',
                    'types' => ['establishment'],
                    'already_imported' => false,
                ]));
        });

        Livewire::actingAs($admin)
            ->test(GooglePlacesImport::class)
            ->set('neighborhoodId', $neighborhood->id)
            ->call('search')
            ->set('selected', ['ChIJ_place_001', 'ChIJ_place_unknown'])
            ->call('import');

        $this->assertDatabaseHas('businesses', [
            'google_place_id' => 'ChIJ_place_001',
            'category_id' => $food->id,
        ]);
        $this->assertDatabaseHas('businesses', [
            'google_place_id' => 'ChIJ_place_unknown',
            'category_id' => $other->id,
        ]);
        $this->assertDatabaseHas('business_categories', [
            'business_id' => Business::where('google_place_id', 'ChIJ_place_001')->value('id'),
            'category_id' => $food->id,
        ]);
    }

    public function test_search_requires_neighborhood(): void
    {
        $admin = $this->makeAdmin();

        Livewire::actingAs($admin)
            ->test(GooglePlacesImport::class)
            ->set('neighborhoodId', 0)
            ->call('search')
            ->assertHasErrors(['neighborhoodId']);
    }

    public function test_after_import_already_imported_flag_is_updated(): void
    {
        Queue::fake();

        $admin = $this->makeAdmin();
        $category = $this->makeCategory();
        $neighborhood = $this->makeNeighborhood();

        $this->mock(GooglePlacesService::class, function (MockInterface $mock) {
            $mock->shouldReceive('searchNearbySimple')
                ->once()
                ->andReturn($this->fakePlaces());
        });

        $component = Livewire::actingAs($admin)
            ->test(GooglePlacesImport::class)
            ->set('neighborhoodId', $neighborhood->id)
            ->set('categoryId', $category->id)
            ->call('search')
            ->set('selected', ['ChIJ_place_001'])
            ->call('import');

        $results = $component->get('results');
        $place1 = collect($results)->firstWhere('place_id', 'ChIJ_place_001');

        $this->assertTrue($place1['already_imported']);
    }

    public function test_selecting_neighborhood_fills_coordinates(): void
    {
        $admin = $this->makeAdmin();
        $neighborhood = $this->makeNeighborhood([
            'latitude' => -22.9068,
            'longitude' => -43.1729,
        ]);

        Livewire::actingAs($admin)
            ->test(GooglePlacesImport::class)
            ->set('neighborhoodId', $neighborhood->id)
            ->assertSet('lat', (float) $neighborhood->latitude)
            ->assertSet('lng', (float) $neighborhood->longitude);
    }
}
