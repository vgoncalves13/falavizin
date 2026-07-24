<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_expose_installation_metadata(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('rel="manifest" href="/manifest.webmanifest"', false)
            ->assertSee('name="theme-color" content="#FD5C3E"', false)
            ->assertSee('name="pwa-install-safe" content="true"', false)
            ->assertSee('data-pwa-install-prompt', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('rel="manifest" href="/manifest.webmanifest"', false)
            ->assertDontSee('data-pwa-install-prompt', false);
    }

    public function test_manifest_contains_required_installation_properties_and_icons(): void
    {
        $manifest = json_decode(
            file_get_contents(public_path('manifest.webmanifest')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame('FalaVizin', $manifest['name']);
        $this->assertSame('FalaVizin', $manifest['short_name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertContains('maskable', array_column($manifest['icons'], 'purpose'));

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_service_worker_only_falls_back_for_same_origin_get_navigations(): void
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString("request.method !== 'GET'", $serviceWorker);
        $this->assertStringContainsString("request.mode !== 'navigate'", $serviceWorker);
        $this->assertStringContainsString('new URL(request.url).origin !== self.location.origin', $serviceWorker);
        $this->assertStringNotContainsString('cache.put(request', $serviceWorker);
        $this->assertStringContainsString('caches.match(OFFLINE_URL)', $serviceWorker);
    }
}
