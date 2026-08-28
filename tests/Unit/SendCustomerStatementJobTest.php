<?php

namespace Tests\Unit;

use App\Jobs\SendCustomerStatement;
use App\Mail\CustomerStatementMail;
use App\Services\StatementService;
use Illuminate\Support\Facades\Mail;
use Mockery\MockInterface;
use Tests\TestCase;

class SendCustomerStatementJobTest extends TestCase
{
    public function test_the_job_generates_and_emails_the_statement_pdf(): void
    {
        Mail::fake();

        $statement = [
            'company' => ['name' => 'Primera Food Nigeria Limited', 'address' => '', 'phone' => ''],
            'customer' => [
                'customer_code' => 'D0001',
                'customer_name' => 'Alpha Stores',
                'address' => 'Lagos',
                'city' => 'Lagos',
                'email' => 'alpha@example.com',
            ],
            'period' => ['from' => '2026-07-01', 'to' => '2026-07-31'],
            'summary' => ['opening_balance' => 0, 'credit' => 0, 'debit' => 0, 'closing_balance' => 0],
            'transactions' => [],
        ];

        $service = $this->mock(StatementService::class, function (MockInterface $mock) use ($statement) {
            $mock->shouldReceive('statement')
                ->once()
                ->with('pfnl', 'D0001', '2026-07-01', '2026-07-31')
                ->andReturn($statement);
        });

        $job = new SendCustomerStatement(
            'pfnl',
            'D0001',
            'alpha@example.com',
            '2026-07-01',
            '2026-07-31',
        );

        $job->handle($service);

        Mail::assertSent(CustomerStatementMail::class, function (CustomerStatementMail $mail) {
            return $mail->hasTo('alpha@example.com') && count($mail->attachments()) === 1;
        });
    }
}
