<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'byom:secret:regenerate',
    description: 'Regenerates the local APP_SECRET env',
)]
class ByomSecretRegenerateCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('random_bytes', InputArgument::OPTIONAL, 'Length of generated seed random bytes', 32);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $secret = substr(hash('sha256', random_bytes($input->getArgument('random_bytes'))), 0, 32);

        shell_exec('sed -i -E "s/^APP_SECRET=.{32}$/APP_SECRET=' . $secret . '/" .env.local');
        $io->success('New APP_SECRET was generated.');

        return Command::SUCCESS;
    }
}
