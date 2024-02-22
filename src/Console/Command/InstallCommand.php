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

        $filesystem = new Filesystem();

        // TODO: Composer in Docker Compose?
        $process = Process::create(
            command: $this->getCommand(
                filesystem: $filesystem,
                language: $this->getProjectLanguage($filesystem, $workingDir, $input),
                workingDir: $workingDir,
            ),
            extraArgs: explode(separator: ' ', string: $extraArgs),
            workingDir: $workingDir,
        );

        $process->setTimeout(null);
        $process->run();

        return Command::SUCCESS;
    }

    /**
     * @param Filesystem $filesystem
     * @param non-empty-string $language
     * @param non-empty-string $workingDir
     * @return non-empty-array<int, non-empty-string>
     */
    private function getCommand(Filesystem $filesystem, string $language, string $workingDir): array
    {
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

    /**
     * @param Filesystem $filesystem
     * @param non-empty-string $workingDir
     * @param InputInterface $input
     * @return non-empty-string
     */
    private function getProjectLanguage(Filesystem $filesystem, string $workingDir, InputInterface $input): string {
        $projectLanguage = null;

        // Determine the language based on the files.
        if ($filesystem->exists($workingDir.'/composer.json')) {
            $projectLanguage = ProjectLanguage::PHP->value;
        } elseif ($filesystem->exists($workingDir.'/package.json')) {
            $projectLanguage = ProjectLanguage::JavaScript->value;
        }

        return $input->getOption('language') ?? $projectLanguage;
    }
}
