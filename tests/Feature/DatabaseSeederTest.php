<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seed_creates_categories_without_demo_users(): void
    {
        $this->app['env'] = 'production';

        app(DatabaseSeeder::class)->run();

        $this->assertGreaterThan(0, Category::count());
        $this->assertDatabaseCount('users', 0);
    }

    public function test_demo_seed_requires_an_explicit_password(): void
    {
        config()->set('app.demo_user_password');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DEMO_USER_PASSWORD must be configured');

        app(DatabaseSeeder::class)->run();
    }

    public function test_demo_seed_uses_the_configured_password(): void
    {
        config()->set('app.demo_user_password', 'configured-demo-password');

        app(DatabaseSeeder::class)->run();

        $admin = User::where('email', 'admin@hudobairro.com.br')->firstOrFail();

        $this->assertTrue(Hash::check('configured-demo-password', $admin->password));
    }
}
