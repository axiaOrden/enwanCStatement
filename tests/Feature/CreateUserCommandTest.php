<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_application_user_can_be_created_from_the_backend_console(): void
    {
        $this->artisan('users:create', [
            'name' => 'Finance Admin',
            'email' => 'ADMIN@EXAMPLE.COM',
        ])
            ->expectsQuestion('Password', 'secure-password')
            ->expectsQuestion('Confirm password', 'secure-password')
            ->assertSuccessful();

        $user = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertSame('Finance Admin', $user->name);
        $this->assertTrue(Hash::check('secure-password', $user->password));
    }

    public function test_backend_user_creation_rejects_an_existing_email(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $this->artisan('users:create', [
            'name' => 'Another Admin',
            'email' => 'admin@example.com',
        ])
            ->expectsQuestion('Password', 'secure-password')
            ->expectsQuestion('Confirm password', 'secure-password')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
    }
}
