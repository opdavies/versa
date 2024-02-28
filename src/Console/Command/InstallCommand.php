<?php

namespace App\Console\Command;

use App\Action\DeterminePackageManager;
use App\Action\DetermineProjectLanguage;
use App\Enum\PackageManager;
use App\Enum\ProjectLanguage;
use App\Process\Process;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    description: 'Install the project\'s dependencies',
    name: 'install',
)]
final class InstallCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $args = $input->getOption('args');
        $language = $input->getOption('language');
        $workingDir = $input->getOption('working-dir');

        if ($language === null) {
            $language = (new DetermineProjectLanguage(
                filesystem: $this->filesystem,
                workingDir: $workingDir,
            ))->getLanguage();
        }

        assert(
            assertion: ProjectLanguage::isValid($language),
            description: sprintf('%s is not a supported language.', $language),
        );

        // TODO: Composer in Docker Compose?
        $process = Process::create(
            args: explode(separator: ' ', string: strval($args)),
            command: $this->getCommand(language: $language, workingDir: $workingDir),
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
     * @throws RuntimeException If the lanuage cannot be determined.
     */
    private function getCommand(string $language, string $workingDir): array
    {
        if ($language === ProjectLanguage::PHP->value) {
            return ['composer', 'install'];
        } elseif ($language === ProjectLanguage::JavaScript->value) {
            $packageManager = new DeterminePackageManager(
                filesystem: $this->filesystem,
                projectLanguage: $language,
                workingDir: $workingDir,
            );

            switch ($packageManager->getPackageManager()) {
                case PackageManager::pnpm->value:
                    return ['pnpm', 'install'];

                case PackageManager::yarn->value:
                    return ['yarn'];

                default:
                    return ['npm', 'install'];
            }
        }

        // TODO: add a test to ensure the exception is thrown?
        throw new RuntimeException('Project language cannot be determined.');
    }
}
