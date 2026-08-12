<?php

namespace Tests\Feature;

use App\Actions\GrantFounderStatusAction;
use App\Enums\BusinessOnboardingStep;
use App\Models\Business;
use App\Models\BusinessManager;
use App\Models\BusinessPhoto;
use App\Models\Neighborhood;
use App\Models\Setting;
use App\Models\User;
use App\Services\BusinessOnboardingProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessFounderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::set('founder_program_enabled', '1');
        Setting::set('founder_max_participants', '0');
    }

    private function eligibleBusiness(User $owner): Business
    {
        $neighborhood = Neighborhood::factory()->create(['is_active' => true]);

        $business = Business::factory()->claimed()->create([
            'user_id' => $owner->id,
            'neighborhood_id' => $neighborhood->id,
        ]);

        BusinessManager::create([
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'granted_at' => now(),
        ]);

        $progress = new BusinessOnboardingProgress;
        $progress->completeStep($business, BusinessOnboardingStep::BasicDetails, $owner);
        $progress->completeStep($business, BusinessOnboardingStep::OpeningHours, $owner);
        $progress->completeStep($business, BusinessOnboardingStep::ProductsServices, $owner);
        $progress->completeStep($business, BusinessOnboardingStep::InitialAction, $owner);

        BusinessPhoto::create([
            'business_id' => $business->id,
            'path' => 'businesses/own-photo.jpg',
            'is_cover' => false,
            'sort_order' => 0,
            'uploaded_by' => $owner->id,
        ]);

        return $business;
    }

    public function test_incomplete_business_does_not_receive_founder_status(): void
    {
        $owner = User::factory()->create();
        $business = $this->eligibleBusiness($owner);

        $business->onboardingSteps()->where('step', 'initial_action')->delete();

        $granted = (new GrantFounderStatusAction)->execute($business, $owner->id);

        $this->assertFalse($granted);
        $this->assertFalse($business->fresh()->is_founder);
    }

    public function test_promotion_is_not_mandatory_when_another_eligible_action_was_done(): void
    {
        $owner = User::factory()->create();
        $business = $this->eligibleBusiness($owner);

        $granted = (new GrantFounderStatusAction)->execute($business, $owner->id);

        $this->assertTrue($granted);
        $this->assertTrue($business->fresh()->is_founder);
        $this->assertDatabaseCount('promotions', 0);
    }

    public function test_eligible_business_receives_founder_status(): void
    {
        $owner = User::factory()->create();
        $business = $this->eligibleBusiness($owner);

        $granted = (new GrantFounderStatusAction)->execute($business, $owner->id);

        $this->assertTrue($granted);
        $business->refresh();
        $this->assertTrue($business->is_founder);
        $this->assertNotNull($business->founder_granted_at);
    }

    public function test_grant_is_idempotent(): void
    {
        $owner = User::factory()->create();
        $business = $this->eligibleBusiness($owner);

        $action = new GrantFounderStatusAction;

        $action->execute($business, $owner->id);
        $second = $action->execute($business->fresh(), $owner->id);

        $this->assertFalse($second);
        $this->assertTrue($business->fresh()->is_founder);
    }

    public function test_business_outside_period_does_not_receive_founder_status(): void
    {
        Setting::set('founder_program_starts_at', now()->addDays(10)->toDateString());

        $owner = User::factory()->create();
        $business = $this->eligibleBusiness($owner);

        $granted = (new GrantFounderStatusAction)->execute($business, $owner->id);

        $this->assertFalse($granted);
        $this->assertFalse($business->fresh()->is_founder);
    }

    public function test_configured_limit_is_respected(): void
    {
        Setting::set('founder_max_participants', '1');

        $owner = User::factory()->create();
        $first = $this->eligibleBusiness($owner);
        $second = $this->eligibleBusiness($owner);

        (new GrantFounderStatusAction)->execute($first, $owner->id);
        $grantedSecond = (new GrantFounderStatusAction)->execute($second, $owner->id);

        $this->assertTrue($first->fresh()->is_founder);
        $this->assertFalse($grantedSecond);
        $this->assertFalse($second->fresh()->is_founder);
    }

    public function test_non_eligible_neighborhood_does_not_receive_founder_status(): void
    {
        $eligibleNeighborhood = $this->eligibleBusiness(User::factory()->create())->localNeighborhood;
        Setting::set('founder_neighborhood_id', (string) $eligibleNeighborhood->id);

        $otherNeighborhood = Neighborhood::factory()->create(['is_active' => true]);
        $owner = User::factory()->create();
        $business = $this->eligibleBusiness($owner);
        $business->update(['neighborhood_id' => $otherNeighborhood->id]);

        $granted = (new GrantFounderStatusAction)->execute($business, $owner->id);

        $this->assertFalse($granted);
        $this->assertFalse($business->fresh()->is_founder);
    }

    public function test_disabled_program_does_not_grant_founder_status(): void
    {
        Setting::set('founder_program_enabled', '0');

        $owner = User::factory()->create();
        $business = $this->eligibleBusiness($owner);

        $granted = (new GrantFounderStatusAction)->execute($business, $owner->id);

        $this->assertFalse($granted);
        $this->assertFalse($business->fresh()->is_founder);
    }

    public function test_founder_badge_is_shown_publicly_only_when_granted(): void
    {
        $owner = User::factory()->create();
        $business = $this->eligibleBusiness($owner);
        $neighborhood = $business->localNeighborhood;

        $response = $this->get(route('neighborhood.businesses.show', [
            ...$neighborhood->routeParameters(),
            'business' => $business,
        ]));

        $response->assertOk();
        $response->assertDontSee('Comércio Fundador');

        (new GrantFounderStatusAction)->execute($business, $owner->id);

        $response = $this->get(route('neighborhood.businesses.show', [
            ...$neighborhood->routeParameters(),
            'business' => $business,
        ]));

        $response->assertOk();
        $response->assertSee('Comércio Fundador');
    }
}
