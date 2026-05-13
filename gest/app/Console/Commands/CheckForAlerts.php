<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckForAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-for-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for conditions that should trigger alerts';

    /**
     * Execute the console command.
     */
    public function handle(AlertService $alertService): int
    {
        $this->info('Checking for expiring subscriptions...');
        $alertService->checkForExpiringSubscriptions();

        $this->info('Checking for low session counts...');
        $alertService->checkForLowSessions();

        $this->info('Checking for expiring SaaS subscriptions...');
        $alertService->checkForExpiringSaasSubscriptions();

        $this->info('Alert checks completed.');

        return Command::SUCCESS;
    }
}
