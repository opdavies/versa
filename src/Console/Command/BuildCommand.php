<?php

namespace App\Console\Command;

use App\Action\DetermineProjectLanguage;
use App\Enum\ProjectLanguage;
use App\Enum\ProjectType;
use App\Process\Process;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    description: 'Install the project\'s dependencies',
    name: 'build',
)]
final class BuildCommand extends AbstractCommand
{
    public static string $description = '';

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectType = null;

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

        // Attempt to prepopulate some of the options, such as the project type
        // based on its dependencies.
        // TODO: move this logic to a service so it can be tested.
        if ($language === ProjectLanguage::PHP->value) {
            $json = json_decode(
                json: strval(file_get_contents($workingDir.'/composer.json')),
                associative: true,
            );

            /** @var non-empty-array<int, non-empty-string> */
            $dependencies = array_keys($json['require']);

            if ($this->isDrupalProject($dependencies)) {
                $projectType = ProjectType::Drupal->value;
            } elseif ($this->isSculpinProject($dependencies)) {
                $projectType = ProjectType::Sculpin->value;
            } elseif ($this->isSymfonyProject($dependencies)) {
                $projectType = ProjectType::Symfony->value;
            }
        } elseif ($this->filesystem->exists($workingDir.'/fractal.config.js')) {
            $language = ProjectLanguage::JavaScript->value;
            $projectType = ProjectType::Fractal->value;
        }

        // Even if the project type is found automatically, still override it
        // with the option value if there is one.
        $projectType = $input->getOption('type') ?? $projectType;

        assert(
            assertion: ProjectType::isValid($projectType),
            description: sprintf('%s is not a supported project type.', $projectType),
        );

        $isDockerCompose = $this->filesystem->exists($workingDir . '/docker-compose.yaml');

        switch ($language) {
            case ProjectLanguage::PHP->value:
                switch ($projectType) {
                    case ProjectType::Drupal->value:
                        if ($isDockerCompose) {
                            $process = Process::create(
                                args: $args,
                                command: ['docker', 'compose', 'build'],
                                workingDir: $workingDir,
                            );

                            $process->run();
                        }
                        break;

                    case ProjectType::Symfony->value:
                        // TODO: run humbug/box if added to generate a phar?
                        throw new RuntimeException('No build command set for Symfony projects.');

                    case ProjectType::Sculpin->value:
                        $process = Process::create(
                            args: $args,
                            command: ['./vendor/bin/sculpin', 'generate'],
                            workingDir: $workingDir,
                        );

                        $process->run();
                        break;
                }

            case ProjectLanguage::JavaScript->value:
                switch ($projectType) {
                    case ProjectType::Fractal->value:
                        $process = Process::create(
                            args: $args,
                            command: ['npx', 'fractal', 'build'],
                            workingDir: $workingDir,
                        );

                        $process->run();
                        break;
                }
                break;
        }

        return Command::SUCCESS;
    }

   /**
    * @param non-empty-string[] $dependencies
    */
    private function isDrupalProject(array $dependencies): bool
    {
        return in_array(needle: 'drupal/core', haystack: $dependencies, strict: true)
            || in_array(needle: 'drupal/core-recommended', haystack: $dependencies, strict: true);
    }

    /**
     * @param non-empty-string[] $dependencies
     */
    private function isSculpinProject(array $dependencies): bool
    {
        return in_array(needle: 'sculpin/sculpin', haystack: $dependencies, strict: true);
    }

    /**
     * @param non-empty-string[] $dependencies
     */
    private function isSymfonyProject(array $dependencies): bool
    {
        return in_array(needle: 'symfony/framework-bundle', haystack: $dependencies, strict: true);
    }
}
