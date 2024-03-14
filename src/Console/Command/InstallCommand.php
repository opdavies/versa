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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    description: 'Install the project\'s dependencies',
    name: 'install',
)]
final class InstallCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $args = $input->getArgument('*');
        $language = $input->getOption('language');
        $workingDir = $input->getOption('working-dir');

        if ($language === null) {
            $languages = (new DetermineProjectLanguage(
                filesystem: $this->filesystem,
                workingDir: $workingDir,
            ))->getLanguages();

            // TODO: if more than one language is found, prompt to select which
            // language to use. For now, always return the first language, which
            // is consistent with what happens before this refactor.
            $language = $languages->first()->value;

            // TODO: throw an Exception if no language is found.

            // TODO: if a project uses multiple languages, ask which language to use
            // instead of using a default value?
            //
            // Currently, PHP will always be used over JavaScript if both are in the
            // same project and an override, such as `versa install -l javascript`
            // needs to be used to install the JavaScript/node dependencies.
            //
            // This could mean that `DetermineProjectLanguage` changes to
            // `DetermineProjectLanguages` and returns a Collection of languages.
            // If a single language is found, it is used. If multiple languages are
            // found, the prompt is used to select which language to use.
            if (false && $languages->count() > 1) {
                $io = new SymfonyStyle($input, $output);

                $choices = $languages
                    ->mapWithKeys(fn (ProjectLanguage $language): array => [$language->value => $language->name])
                    ->sort();

                $language = $io->choice(
                    question: 'Which language should I install',
                    choices: $choices->toArray(),
                );
            }
        }

        assert(
            assertion: ProjectLanguage::isValid($language),
            description: sprintf('%s is not a supported language.', $language),
        );

        // TODO: Composer in Docker Compose?
        $process = Process::create(
            args: $args,
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
