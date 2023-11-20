<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\EtatUpdateService;

class UpdateEtatsCommand extends Command
{
    protected static $defaultName = 'app:update-etats';
    private $etatUpdateService;

    public function __construct(EtatUpdateService $etatUpdateService)
    {
        $this->etatUpdateService = $etatUpdateService;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Mise à jour des états des sorties');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->etatUpdateService->mettreAJourEtatsSorties();
        $output->writeln('Mise à jour des états des sorties effectuée avec succès.');

        return Command::SUCCESS;
    }
}
