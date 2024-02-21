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
        parent::execute($input, $output);

        // TODO: move this logic to a service so it can be tested.
        $json = json_decode(
            json: strval(file_get_contents($this->workingDir.'/composer.json')),
            associative: true,
        );

        // TODO: what if there are no dev dependencies?
        $devDependencies = array_keys($json['require-dev']);

        // TODO: Pest and Behat.
        if (in_array(needle: 'brianium/paratest', haystack: $devDependencies, strict: true)) {
            $command = ['./vendor/bin/paratest'];
        } else {
            $command = ['./vendor/bin/phpunit'];
        }

        // TODO: commands in Docker Compose?
        $process = Process::create(
            command: $command,
            extraArgs: $this->extraArgs,
            workingDir: $this->workingDir,
        );

        $process->run();

        return Command::SUCCESS;
    }
}
