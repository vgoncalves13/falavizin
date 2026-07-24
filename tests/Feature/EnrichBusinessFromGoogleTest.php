<?php

namespace Tests\Feature;

use App\Jobs\EnrichBusinessFromGoogle;
use App\Models\Business;
use App\Models\Neighborhood;
use App\Services\GooglePlacesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class EnrichBusinessFromGoogleTest extends TestCase
{
    use RefreshDatabase;

    public function test_rate_limit_is_retried_with_progressive_backoff(): void
    {
        Http::fake(fn () => Http::response(status: 429));

        $business = Business::factory()->create(['google_place_id' => 'rate-limited-place']);
        $job = new EnrichBusinessFromGoogle($business->id);

        $this->assertSame(4, $job->tries);
        $this->assertSame([60, 300, 900], $job->backoff);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('429');

        $job->handle(app(GooglePlacesService::class));
    }

    public function test_job_does_not_overwrite_neighborhood_id_when_already_set(): void
    {
        $neighborhood = Neighborhood::factory()->create();
        $business = Business::factory()->create([
            'google_place_id' => 'ChIJ_enrich_test',
            'neighborhood_id' => $neighborhood->id,
            'phone' => null,
            'website' => null,
        ]);

        $job = new EnrichBusinessFromGoogle($business->id);

        Http::fake([
            '*' => Http::response([
                'nationalPhoneNumber' => '(21) 3333-4444',
                'websiteUri' => 'https://example.com',
                'regularOpeningHours' => [
                    'weekdayDescriptions' => [
                        'Segunda-feira: 08:00 – 18:00',
                    ],
                ],
            ], 200),
        ]);

        $job->handle(app(GooglePlacesService::class));

        $business->refresh();

        $this->assertSame($neighborhood->id, $business->neighborhood_id);
        $this->assertSame(['(21) 3333-4444'], $business->phone);
        $this->assertSame('https://example.com', $business->website);
    }
}
