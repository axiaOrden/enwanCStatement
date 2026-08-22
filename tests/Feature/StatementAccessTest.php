<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatementAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/statements')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_reach_the_statement_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        // Isolate this routing/authentication test from the external SAP snapshot tables.
        $this->mock(\App\Services\StatementService::class, function ($mock) {
            $mock->shouldReceive('organizations')->once()->andReturn([]);
        });

        $this->get('/statements')->assertOk()->assertSee('Customer statements');
    }
}
