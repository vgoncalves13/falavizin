<?php

namespace Tests\Feature;

use App\Enums\BusinessOnboardingStep;
use App\Livewire\Business\BusinessOnboardingWizard;
use App\Livewire\Business\OnboardingBanner;
use App\Models\Business;
use App\Models\BusinessManager;
use App\Models\BusinessPhoto;
use App\Models\User;
use App\Services\BusinessOnboardingProgress;
use App\Services\BusinessQrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessOnboardingTest extends TestCase
{
    use RefreshDatabase;

    private function managedBusinessFor(User $user): Business
    {
        $business = Business::factory()->claimed()->create(['user_id' => $user->id]);

        BusinessManager::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'granted_at' => now(),
        ]);

        return $business;
    }

    public function test_imported_business_starts_without_steps_confirmed(): void
    {
        $business = Business::factory()->create([
            'claimed' => false,
            'user_id' => null,
            'opening_hours' => [['day' => 'Segunda-feira', 'open' => '08:00', 'close' => '18:00', 'closed' => false]],
        ]);

        $progress = new BusinessOnboardingProgress;

        $this->assertFalse($progress->isComplete($business));
        $this->assertSame(0, $progress->completedSteps($business));
        $this->assertSame(BusinessOnboardingStep::BasicDetails, $progress->nextStep($business));
    }

    public function test_confirming_basic_details_completes_the_step(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->call('confirmBasicDetails');

        $record = $business->onboardingSteps()->where('step', 'basic_details')->firstOrFail();
        $this->assertNotNull($record->completed_at);
        $this->assertSame($user->id, $record->completed_by);
        $this->assertSame($business->name, $record->data['name']);
    }

    public function test_imported_opening_hours_require_explicit_confirmation(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);
        $business->update(['opening_hours' => [
            ['day' => 'Segunda-feira', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
        ]]);

        $progress = new BusinessOnboardingProgress;

        $this->assertFalse($progress->stepStatus($business, BusinessOnboardingStep::OpeningHours)['completed']);

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->call('saveOpeningHours');

        $this->assertTrue((new BusinessOnboardingProgress)->stepStatus($business, BusinessOnboardingStep::OpeningHours)['completed']);
    }

    public function test_imported_photo_does_not_complete_own_photo_step(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        BusinessPhoto::create([
            'business_id' => $business->id,
            'path' => 'businesses/imported.jpg',
            'is_cover' => false,
            'sort_order' => 0,
            'uploaded_by' => null,
        ]);

        $this->assertFalse((new BusinessOnboardingProgress)->stepStatus($business, BusinessOnboardingStep::OwnPhoto)['completed']);
    }

    public function test_authorized_upload_completes_own_photo_step(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->set('newPhotos', [UploadedFile::fake()->image('loja.jpg', 800, 600)])
            ->call('uploadPhotos');

        $this->assertTrue((new BusinessOnboardingProgress)->stepStatus($business, BusinessOnboardingStep::OwnPhoto)['completed']);
        $this->assertDatabaseHas('business_photos', [
            'business_id' => $business->id,
            'uploaded_by' => $user->id,
        ]);
    }

    public function test_progress_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        $progress = new BusinessOnboardingProgress;

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->call('confirmBasicDetails');

        $this->assertSame(20, $progress->progress($business));
        $this->assertSame(BusinessOnboardingStep::OpeningHours, $progress->nextStep($business));

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->call('saveOpeningHours');

        $this->assertSame(40, $progress->progress($business));
        $this->assertSame(BusinessOnboardingStep::OwnPhoto, $progress->nextStep($business));
    }

    public function test_initial_action_can_be_completed_without_promotion(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->call('completeAction', 'share')
            ->assertRedirect($business->canonicalUrl());

        $this->assertDatabaseMissing('business_onboarding_steps', ['business_id' => $business->id, 'step' => 'initial_action']);

        $this->actingAs($user)
            ->post(route('businesses.share.track', $business))
            ->assertOk();

        $record = $business->onboardingSteps()->where('step', 'initial_action')->firstOrFail();
        $this->assertSame('share', $record->data['action']);
    }

    public function test_share_tracking_requires_manager(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $business = $this->managedBusinessFor($owner);

        $this->actingAs($stranger)
            ->post(route('businesses.share.track', $business))
            ->assertOk();

        $this->assertDatabaseMissing('business_onboarding_steps', ['business_id' => $business->id, 'step' => 'initial_action']);
    }

    public function test_qr_code_is_confirmed_from_the_qr_page(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->call('completeAction', 'qr')
            ->assertRedirect(route('businesses.qr', $business));

        $this->assertDatabaseMissing('business_onboarding_steps', ['business_id' => $business->id, 'step' => 'initial_action']);

        $this->actingAs($user)
            ->post(route('businesses.qr.confirm', $business))
            ->assertRedirect(route('businesses.onboarding', $business));

        $record = $business->onboardingSteps()->where('step', 'initial_action')->firstOrFail();
        $this->assertSame('qr', $record->data['action']);
    }

    public function test_qr_svg_contains_logo_and_brand_colors(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        $png = (new BusinessQrCodeService)->pngFor($business);

        $this->assertNotSame('', $png);
        $image = imagecreatefromstring($png);
        $this->assertNotFalse($image);
        $this->assertSame('image/png', (new \finfo(FILEINFO_MIME_TYPE))->buffer($png));
    }

    public function test_qr_page_renders_svg_for_manager(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        $response = $this->actingAs($user)->get(route('businesses.qr', $business));

        $response->assertOk();
        $response->assertSee('QR Code de '.$business->name);
        $response->assertSee('data:image/png;base64,', escape: false);
    }

    public function test_onboarding_banner_is_shown_on_business_page_for_manager(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);
        $neighborhood = $business->localNeighborhood;

        $response = $this->actingAs($user)->get(route('neighborhood.businesses.show', [
            ...$neighborhood->routeParameters(),
            'business' => $business,
        ]));

        $response->assertOk();
        $response->assertSee('Complete o perfil de');
        $response->assertSee('Continuar configuração');
    }

    public function test_onboarding_banner_is_not_shown_for_strangers(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $business = $this->managedBusinessFor($owner);
        $neighborhood = $business->localNeighborhood;

        $response = $this->actingAs($stranger)->get(route('neighborhood.businesses.show', [
            ...$neighborhood->routeParameters(),
            'business' => $business,
        ]));

        $response->assertOk();
        $response->assertDontSee('Complete o perfil de');
    }

    public function test_share_action_highlights_share_button_on_business_page(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);
        $neighborhood = $business->localNeighborhood;
        $url = route('neighborhood.businesses.show', [
            ...$neighborhood->routeParameters(),
            'business' => $business,
        ]);

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->call('completeAction', 'share')
            ->assertRedirect($business->canonicalUrl());

        $response = $this->actingAs($user)->get($url);

        $response->assertOk();
        $response->assertSee('Compartilhe seu perfil para concluir a configuração');
    }

    public function test_onboarding_banner_reappears_after_dismissal_cooldown(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);
        $neighborhood = $business->localNeighborhood;
        $url = route('neighborhood.businesses.show', [
            ...$neighborhood->routeParameters(),
            'business' => $business,
        ]);

        $this->actingAs($user)->get($url)->assertSee('Complete o perfil de');

        Livewire::actingAs($user)
            ->test(OnboardingBanner::class, ['business' => $business])
            ->call('dismiss');

        $this->actingAs($user)->get($url)->assertDontSee('Complete o perfil de');

        $this->travel(25)->hours();

        $this->actingAs($user)->get($url)->assertSee('Complete o perfil de');
    }

    public function test_onboarding_banner_hides_when_onboarding_complete(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        BusinessPhoto::create([
            'business_id' => $business->id,
            'path' => 'businesses/own-photo.jpg',
            'is_cover' => false,
            'sort_order' => 0,
            'uploaded_by' => $user->id,
        ]);

        $progress = new BusinessOnboardingProgress;
        foreach (BusinessOnboardingStep::ordered() as $step) {
            if ($step !== BusinessOnboardingStep::OwnPhoto) {
                $progress->completeStep($business, $step, $user);
            }
        }

        $this->assertTrue($progress->isComplete($business));

        $neighborhood = $business->localNeighborhood;
        $response = $this->actingAs($user)->get(route('neighborhood.businesses.show', [
            ...$neighborhood->routeParameters(),
            'business' => $business,
        ]));

        $response->assertOk();
        $response->assertDontSee('Complete o perfil de');
    }

    public function test_non_manager_cannot_alter_onboarding(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $business = $this->managedBusinessFor($owner);

        $this->assertFalse($intruder->can('update', $business));

        $response = $this->actingAs($intruder)
            ->get(route('businesses.onboarding', $business));

        $response->assertForbidden();
    }

    public function test_multiple_businesses_keep_separate_progress(): void
    {
        $user = User::factory()->create();
        $first = $this->managedBusinessFor($user);
        $second = $this->managedBusinessFor($user);

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $first])
            ->call('confirmBasicDetails');

        $progress = new BusinessOnboardingProgress;

        $this->assertGreaterThan(0, $progress->completedSteps($first));
        $this->assertSame(0, $progress->completedSteps($second));
        $this->assertSame(BusinessOnboardingStep::BasicDetails, $progress->nextStep($second));
    }

    public function test_saving_products_services_completes_step_and_updates_description(): void
    {
        $user = User::factory()->create();
        $business = $this->managedBusinessFor($user);

        Livewire::actingAs($user)
            ->test(BusinessOnboardingWizard::class, ['business' => $business])
            ->set('description', 'Padaria artesanal com pães integrais e café da manhã.')
            ->call('saveServices');

        $this->assertSame('Padaria artesanal com pães integrais e café da manhã.', $business->fresh()->description);
        $this->assertTrue((new BusinessOnboardingProgress)->stepStatus($business, BusinessOnboardingStep::ProductsServices)['completed']);
    }
}
