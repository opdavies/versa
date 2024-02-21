<?php

namespace App\Console\Command;

use App\Enum\ProjectType;
use App\Process\Process;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class BuildCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectType = null;

        $filesystem = new Filesystem();

        // Attempt to prepopulate some of the options, such as the project type
        // based on its dependencies.
        // TODO: move this logic to a service so it can be tested.
        if ($filesystem->exists($this->workingDir.'/composer.json')) {
            $json = json_decode(
                json: strval(file_get_contents($this->workingDir.'/composer.json')),
                associative: true,
            );

            $dependencies = array_keys($json['require']);

            if (in_array(needle: 'drupal/core', haystack: $dependencies, strict: true) || in_array(needle: 'drupal/core-recommended', haystack: $dependencies, strict: true)) {
                $projectType = ProjectType::Drupal->value;
            } elseif (in_array(needle: 'sculpin/sculpin', haystack: $dependencies, strict: true)) {
                $projectType = ProjectType::Sculpin->value;
            } elseif (in_array(needle: 'symfony/framework-bundle', haystack: $dependencies, strict: true)) {
                $projectType = ProjectType::Symfony->value;
            }
        }

        // Even if the project type is found automatically, still override it with
        // the option value if there is one.
        $projectType = $input->getOption('type') ?? $projectType;

        $isDockerCompose = $filesystem->exists($this->workingDir . '/docker-compose.yaml');

        switch ($projectType) {
            case ProjectType::Drupal->value:
                if ($isDockerCompose) {
                    $process = Process::create(
                        command: ['docker', 'compose', 'build'],
                        extraArgs: $this->extraArgs,
                        workingDir: $this->workingDir,
                    );

                    $process->run();
                }
                break;

            case ProjectType::Symfony->value:
                // TODO: run humbug/box if added to generate a phar?
                throw new RuntimeException('No build command set for Symfony projects.');

            case ProjectType::Sculpin->value:
                $process = Process::create(
                    command: ['./vendor/bin/sculpin', 'generate'],
                    extraArgs: $this->extraArgs,
                    workingDir: $this->workingDir,
                );

                $process->run();
                break;
        }

        return Command::SUCCESS;
    }
}
