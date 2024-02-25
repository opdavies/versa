<?php

namespace App\Console\Command;

use App\Action\DetermineProjectLanguage;
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
        $args = $input->getOption('args');
        $workingDir = $input->getOption('working-dir');

        $language = $input->getOption('language') ?? (new DetermineProjectLanguage(
            filesystem: $this->filesystem,
            workingDir: $workingDir,
        ))->getLanguage();

        $filesystem = new Filesystem();

        // TODO: Composer in Docker Compose?
        $process = Process::create(
            args: explode(separator: ' ', string: strval($args)),
            command: $this->getCommand(
                filesystem: $filesystem,
                language: $language,
                workingDir: $workingDir,
            ),
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
}
