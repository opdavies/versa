<?php

namespace App\Console\Command;

use App\Action\DetermineProjectLanguage;
use App\Enum\ProjectLanguage;
use App\Enum\ProjectType;
use App\Process\Process;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    description: 'Run the project',
    name: 'run',
)]
final class RunCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectType = null;

        $args = $input->getOption('args');
        $workingDir = $input->getOption('working-dir');

        $filesystem = new Filesystem();

        $language = $input->getOption('language') ?? (new DetermineProjectLanguage(
            filesystem: $this->filesystem,
            workingDir: $workingDir,
        ))->getLanguage();

        // Attempt to prepopulate some of the options, such as the project type
        // based on its dependencies.
        // TODO: move this logic to a service so it can be tested.
        if ($language === ProjectLanguage::PHP->value) {
            $json = json_decode(
                json: strval(file_get_contents($workingDir.'/composer.json')),
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
        } elseif ($language === ProjectLanguage::JavaScript->value) {
            if ($filesystem->exists($workingDir.'/fractal.config.js')) {
                $projectType = ProjectType::Fractal->value;
            }
        }

        // Even if the project type is found automatically, still override it
        // with the option value if there is one.
        $projectType = $input->getOption('type') ?? $projectType;

        $filesystem = new Filesystem();
        $isDockerCompose = $filesystem->exists($workingDir . '/docker-compose.yaml');

        if ($isDockerCompose) {
            $process = Process::create(
                args: explode(separator: ' ', string: $args ?? ''),
                command: ['docker', 'compose', 'up'],
                workingDir: $workingDir,
            );

            $process->setTimeout(null);
            $process->run();
        } else {
            switch ($projectType) {
                case ProjectType::Fractal->value:
                    $process = Process::create(
                        args: explode(separator: ' ', string: $args ?? ''),
                        command: ['npx', 'fractal', 'start', '--sync'],
                        workingDir: $workingDir,
                    );

                    $process->setTimeout(null);
                    $process->run();
                    break;

                case ProjectType::Sculpin->value:
                    $process = Process::create(
                        args: explode(separator: ' ', string: $args ?? ''),
                        command: ['./vendor/bin/sculpin', 'generate', '--server', '--watch'],
                        workingDir: $workingDir,
                    );

                    $process->setTimeout(null);
                    $process->run();
                    break;
            }
        }

        return Command::SUCCESS;
    }
}
