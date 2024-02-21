<?php

namespace App\Console\Command;

use App\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class InstallCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $extraArgs = $input->getOption('extra-args');
        $workingDir = $input->getOption('working-dir');

        // TODO: Composer in Docker Compose?
        $process = Process::create(
            command: ['composer', 'install'],
            extraArgs: $extraArgs,
            workingDir: $workingDir,
        );

        $process->setTimeout(null);
        $process->run();

        return Command::SUCCESS;
    }
}
