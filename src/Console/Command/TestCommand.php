<?php

namespace App\Console\Command;

use App\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TestCommand extends AbstractCommand
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $extraArgs = $input->getOption('extra-args');
        $workingDir = $input->getOption('working-dir');

        // TODO: move this logic to a service so it can be tested.
        $json = json_decode(
            json: strval(file_get_contents($workingDir.'/composer.json')),
            associative: true,
        );

        $devDependencies = array_keys($json['require-dev'] ?? []);

        // TODO: Pest and Behat.
        if (in_array(needle: 'brianium/paratest', haystack: $devDependencies, strict: true)) {
            $command = ['./vendor/bin/paratest'];
        } else {
            $command = ['./vendor/bin/phpunit'];
        }

        // TODO: commands in Docker Compose?
        $process = Process::create(
            command: $command,
            extraArgs: $extraArgs,
            workingDir: $workingDir,
        );

        $process->run();

        return Command::SUCCESS;
    }
}
