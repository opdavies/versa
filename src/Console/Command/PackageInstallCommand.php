<?php

namespace App\Console\Command;

use App\Action\DeterminePackageManager;
use App\Action\DetermineProjectLanguage;
use App\Enum\PackageManager;
use App\Enum\ProjectLanguage;
use App\Process\Process;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    description: 'Install a new package',
    name: 'package-install',
)]
final class PackageInstallCommand extends AbstractCommand
{
    public function configure(): void
    {
        $this->addArgument(
            name: 'package-name',
            mode: InputArgument::REQUIRED,
        );

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $args = $input->getArgument('*');
        $workingDir = $input->getOption('working-dir');

        $language = $input->getOption('language') ?? (new DetermineProjectLanguage(
            filesystem: $this->filesystem,
            workingDir: $workingDir,
        ))->getLanguage();

        assert(
            assertion: ProjectLanguage::isValid($language),
            description: sprintf('%s is not a supported language.', $language),
        );

        switch ($language) {
            case ProjectLanguage::PHP->value:
                $process = Process::create(
                    args: $args,
                    command: ['composer', 'require', $input->getArgument('package-name')],
                    workingDir: '.',
                );

                $process->setTimeout(null);
                $process->run();
                break;

            case ProjectLanguage::JavaScript->value:
                $packageManager = new DeterminePackageManager(
                    filesystem: $this->filesystem,
                    projectLanguage: $language,
                    workingDir: $workingDir,
                );

                switch ($packageManager->getPackageManager()) {
                    case PackageManager::pnpm->value:
                        $command = ['pnpm', 'install'];
                        break;

                    case PackageManager::yarn->value:
                        $command = ['yarn', 'add'];
                        break;

                    default:
                        $command = ['npm', 'install'];
                        break;
                }

                $process = Process::create(
                    args: $args,
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
