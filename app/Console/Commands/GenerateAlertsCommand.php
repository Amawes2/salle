<?php

namespace App\Console\Commands;

use App\Models\Gym;
use App\Services\AlertService;
use Illuminate\Console\Command;

class GenerateAlertsCommand extends Command
{
    protected $signature = 'alerts:generate {--gym-id=}';

    protected $description = 'Generate alerts for expiring subscriptions, low sessions, and SaaS payments';

    public function handle(): int
    {
        $alertService = new AlertService;

        if ($gymId = $this->option('gym-id')) {
            $gym = Gym::find($gymId);
            if (! $gym) {
                $this->error("Salle de gym avec ID {$gymId} non trouvée");

                return self::FAILURE;
            }
            $this->generateForGym($alertService, $gym);
        } else {
            $this->generateForAllGyms($alertService);
        }

        $this->info('Alertes générées avec succès');

        return self::SUCCESS;
    }

    private function generateForGym(AlertService $alertService, Gym $gym): void
    {
        $this->info("Génération d'alertes pour {$gym->name}...");

        $this->info('  Vérification des abonnements expirant...');
        $alertService->checkForExpiringSubscriptions($gym);

        $this->info('  Vérification des séances restantes...');
        $alertService->checkForLowSessions($gym);

        $this->info('  Vérification du SaaS...');
        $alertService->checkForExpiringSaasSubscriptions();
    }

    private function generateForAllGyms(AlertService $alertService): void
    {
        $this->info("Génération d'alertes pour toutes les salles...");

        $this->info('  Vérification des abonnements expirant...');
        $alertService->checkForExpiringSubscriptions();

        $this->info('  Vérification des séances restantes...');
        $alertService->checkForLowSessions();

        $this->info('  Vérification du SaaS...');
        $alertService->checkForExpiringSaasSubscriptions();
    }
}
