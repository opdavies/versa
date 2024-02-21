<?php

namespace App\Console\Command;

use App\Enum\ProjectLanguage;
use App\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class InstallCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $extraArgs = $input->getOption('extra-args');
        $workingDir = $input->getOption('working-dir');

        // TODO: What to do if a project contains multiple languages?
        // e.g. a composer.lock file (PHP) and pnpm-lock.yaml file (JS)?

        // TODO: validate the language is an allowed value.

        // TODO: Composer in Docker Compose?
        $process = Process::create(
            command: $this->getCommand(
                language: $input->getOption('language'),
                workingDir: $workingDir,
            ),
            extraArgs: $extraArgs,
            workingDir: $workingDir,
        );

        $process->setTimeout(null);
        $process->run();

        return Command::SUCCESS;
    }

    /**
     * @param non-empty-string $language
     * @param non-empty-string $workingDir
     * @return non-empty-array<int, non-empty-string>
     */
    private function getCommand(string $language, string $workingDir): array
    {
        $filesystem = new Filesystem();

        if ($language === ProjectLanguage::JavaScript->value) {
            if ($filesystem->exists($workingDir.'/yarn.lock')) {
                return ['yarn'];
            } elseif ($filesystem->exists($workingDir.'/pnpm-lock.yaml')) {
                return ['pnpm', 'install'];
            } else {
                return ['npm', 'install'];
            }
        }

        return ['composer', 'install'];
    }

}
