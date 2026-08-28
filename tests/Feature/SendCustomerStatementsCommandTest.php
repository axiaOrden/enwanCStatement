<?php

namespace Tests\Feature;

use App\Jobs\SendCustomerStatement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SendCustomerStatementsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('snapshot_customers', function (Blueprint $table) {
            $table->id();
            $table->string('sales_org');
            $table->string('sold_to_party');
            $table->string('customer_name')->nullable();
            $table->string('email')->nullable();
        });
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_it_queues_one_statement_job_per_customer_with_a_valid_email(): void
    {
        Queue::fake();

        DB::table('snapshot_customers')->insert([
            ['sales_org' => 'PFNL', 'sold_to_party' => 'D0001', 'customer_name' => 'Alpha Stores', 'email' => 'alpha@example.com'],
            ['sales_org' => 'PFNL', 'sold_to_party' => 'D0001', 'customer_name' => 'Alpha Stores', 'email' => 'alpha@example.com'],
            ['sales_org' => 'pfnl', 'sold_to_party' => 'D0002', 'customer_name' => 'Beta Stores', 'email' => 'beta@example.com'],
            ['sales_org' => 'PFNL', 'sold_to_party' => 'D0003', 'customer_name' => 'Invalid Stores', 'email' => 'not-an-email'],
            ['sales_org' => 'PFNL', 'sold_to_party' => 'D0004', 'customer_name' => 'No Email Stores', 'email' => null],
            ['sales_org' => 'EMANL', 'sold_to_party' => 'D0005', 'customer_name' => 'Other Org', 'email' => 'other@example.com'],
        ]);

        $this->artisan('statements:send', [
            'org' => 'pfnl',
            '--from' => '2026-07-01',
            '--to' => '2026-07-31',
        ])->assertSuccessful();

        Queue::assertPushedOn('statements', SendCustomerStatement::class);
        Queue::assertPushed(SendCustomerStatement::class, 2);
        Queue::assertPushed(SendCustomerStatement::class, function (SendCustomerStatement $job) {
            return $job->salesOrg === 'pfnl'
                && $job->soldToParty === 'D0001'
                && $job->email === 'alpha@example.com'
                && $job->from === '2026-07-01'
                && $job->to === '2026-07-31';
        });
    }

    public function test_dry_run_does_not_dispatch_jobs(): void
    {
        Queue::fake();

        DB::table('snapshot_customers')->insert([
            'sales_org' => 'PFNL',
            'sold_to_party' => 'D0001',
            'customer_name' => 'Alpha Stores',
            'email' => 'alpha@example.com',
        ]);

        $this->artisan('statements:send', [
            'org' => 'pfnl',
            '--from' => '2026-07-01',
            '--to' => '2026-07-31',
            '--dry-run' => true,
        ])->assertSuccessful();

        Queue::assertNothingPushed();
    }

    public function test_it_defaults_to_the_complete_previous_calendar_month(): void
    {
        Queue::fake();
        CarbonImmutable::setTestNow('2026-08-28 10:00:00');

        DB::table('snapshot_customers')->insert([
            'sales_org' => 'PFNL',
            'sold_to_party' => 'D0001',
            'customer_name' => 'Alpha Stores',
            'email' => 'alpha@example.com',
        ]);

        $this->artisan('statements:send', ['org' => 'pfnl'])->assertSuccessful();

        Queue::assertPushed(SendCustomerStatement::class, function (SendCustomerStatement $job) {
            return $job->from === '2026-07-01' && $job->to === '2026-07-31';
        });

    }

    public function test_it_rejects_an_invalid_date_range(): void
    {
        Queue::fake();

        $this->artisan('statements:send', [
            'org' => 'pfnl',
            '--from' => '2026-07-31',
            '--to' => '2026-07-01',
        ])->assertFailed();

        Queue::assertNothingPushed();
    }
}
