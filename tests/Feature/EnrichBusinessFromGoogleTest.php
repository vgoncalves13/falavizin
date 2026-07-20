<?php

namespace Tests\Feature;

use App\Jobs\EnrichBusinessFromGoogle;
use App\Models\Business;
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
}
