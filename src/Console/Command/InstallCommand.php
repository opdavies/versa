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
        parent::execute($input, $output);

        // TODO: Composer in Docker Compose?
        $process = Process::create(
            command: ['composer', 'install'],
            extraArgs: $this->extraArgs,
            workingDir: $this->workingDir,
        );

        $process->setTimeout(null);
        $process->run();

        return Command::SUCCESS;
    }
}
