<?php

namespace App\Console\Commands;

use App\Jobs\SendCustomerStatement;
use App\Services\StatementService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class SendCustomerStatements extends Command
{
    protected $signature = 'statements:send
        {org : Sales organization code, for example pfnl}
        {--from= : Statement start date in YYYY-MM-DD format; defaults to the first day of last month}
        {--to= : Statement end date in YYYY-MM-DD format; defaults to the last day of last month}
        {--queue=statements : Queue name used for dispatched jobs}
        {--dry-run : Show eligible recipients without dispatching jobs}
        {--sync : Generate and send each statement in the current process}';

    protected $description = 'Queue customer statement emails for every customer with a valid email address';

    public function handle(StatementService $statements): int
    {
        $salesOrg = strtolower(trim((string) $this->argument('org')));
        $lastMonth = CarbonImmutable::now()->subMonthNoOverflow();
        $from = trim((string) ($this->option('from') ?: $lastMonth->startOfMonth()->toDateString()));
        $to = trim((string) ($this->option('to') ?: $lastMonth->endOfMonth()->toDateString()));
        $queue = trim((string) $this->option('queue'));

        $validator = Validator::make(
            compact('salesOrg', 'from', 'to', 'queue'),
            [
                'salesOrg' => ['required', 'string', 'max:20'],
                'from' => ['required', 'date_format:Y-m-d'],
                'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
                'queue' => ['required', 'string', 'max:255'],
            ],
            ['to.after_or_equal' => 'The --to date must be on or after the --from date.'],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $sync = (bool) $this->option('sync');
        $eligible = 0;
        $skipped = 0;

        $this->components->info(sprintf(
            '%s customer statements for %s from %s to %s.',
            $dryRun ? 'Checking' : ($sync ? 'Sending' : 'Queueing'),
            strtoupper($salesOrg),
            $from,
            $to,
        ));

        foreach ($statements->statementRecipients($salesOrg) as $recipient) {
            $email = trim((string) ($recipient['email'] ?? ''));

            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $skipped++;
                $this->warn(sprintf(
                    'Skipped %s: invalid email address "%s".',
                    $recipient['sold_to_party'],
                    $email,
                ));

                continue;
            }

            $eligible++;

            if ($dryRun) {
                $this->line(sprintf(
                    '  %s <%s>',
                    $recipient['customer_name'] ?: $recipient['sold_to_party'],
                    $email,
                ));

                continue;
            }

            $job = new SendCustomerStatement(
                $salesOrg,
                (string) $recipient['sold_to_party'],
                $email,
                $from,
                $to,
            );

            if ($sync) {
                dispatch_sync($job);
            } else {
                dispatch($job->onQueue($queue));
            }
        }

        if ($eligible === 0) {
            $this->components->warn('No customers with valid email addresses were found.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->components->info(sprintf(
            '%d statement%s %s%s.',
            $eligible,
            $eligible === 1 ? '' : 's',
            $dryRun ? 'eligible' : ($sync ? 'sent' : 'queued'),
            $skipped > 0 ? "; {$skipped} invalid address".($skipped === 1 ? ' skipped' : 'es skipped') : '',
        ));

        if (! $dryRun && ! $sync) {
            $this->line("Run <comment>php artisan queue:work --queue={$queue}</comment> to process the jobs.");
        }

        return self::SUCCESS;
    }
}
