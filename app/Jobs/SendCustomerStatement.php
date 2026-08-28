<?php

namespace App\Jobs;

use App\Mail\CustomerStatementMail;
use App\Services\StatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCustomerStatement implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 3600;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly string $salesOrg,
        public readonly string $soldToParty,
        public readonly string $email,
        public readonly string $from,
        public readonly string $to,
    ) {}

    public function uniqueId(): string
    {
        return implode(':', [$this->salesOrg, $this->soldToParty, $this->from, $this->to]);
    }

    public function handle(StatementService $statements): void
    {
        $statement = $statements->statement(
            $this->salesOrg,
            $this->soldToParty,
            $this->from,
            $this->to,
        );

        $pdf = Pdf::loadView('statements.document', $statement)
            ->setPaper('a4')
            ->output();

        Mail::to($this->email)->send(new CustomerStatementMail($statement, $pdf));
    }
}
