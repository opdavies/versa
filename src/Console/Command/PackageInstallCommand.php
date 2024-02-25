<?php

namespace App\Console\Command;

use App\Action\DetermineProjectLanguage;
use App\Enum\ProjectLanguage;
use App\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PackageInstallCommand extends AbstractCommand
{
    public function configure(): void
    {
        parent::configure();

        $this->addArgument(
            name: 'package-name',
            mode: InputArgument::REQUIRED,
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $args = $input->getOption('args');
        $workingDir = $input->getOption('working-dir');

        $language = $input->getOption('language') ?? (new DetermineProjectLanguage(
            filesystem: $this->filesystem,
            workingDir: $workingDir,
        ))->getLanguage();

        switch ($language) {
            case ProjectLanguage::PHP->value:
                $process = Process::create(
                    args: explode(separator: ' ', string: $args ?? ''),
                    command: ['composer', 'require', $input->getArgument('package-name')],
                    workingDir: '.',
                );

                $process->setTimeout(null);
                $process->run();
                break;

            case ProjectLanguage::JavaScript->value:
                if ($this->filesystem->exists($workingDir.'/yarn.lock')) {
                    $command = ['yarn', 'add'];
                } elseif ($this->filesystem->exists($workingDir.'/pnpm-lock.yaml')) {
                    $command = ['pnpm', 'install'];
                } else {
                    $command = ['npm', 'install'];
                }

                $process = Process::create(
                    args: explode(separator: ' ', string: $args ?? ''),
                    command: $command,
                    workingDir: $workingDir,
                );
                $process->setTimeout(null);
                $process->run();
                break;
        }

        return Command::SUCCESS;
    }
}