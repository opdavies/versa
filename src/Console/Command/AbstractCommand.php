<?php

namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    protected ?string $extraArgs;

    protected string $workingDir;

    protected function configure(): void
    {
        $this->addOption(
            name: 'extra-args',
            shortcut: 'a',
            mode: InputArgument::OPTIONAL,
            description: 'Any additonal arguments to pass to the command.',
        );

        $this->addOption(
            name: 'type',
            shortcut: 't',
            mode: InputArgument::OPTIONAL,
            description: 'The project type',
            suggestedValues: ['drupal', 'sculpin'],
        );

        $this->addOption(
            name: 'working-dir',
            shortcut: 'd',
            mode: InputArgument::OPTIONAL,
            description: 'The project\'s working directory',
            default: '.',
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->extraArgs = $input->getOption('extra-args');
        $this->workingDir = $input->getOption('working-dir');

        return Command::SUCCESS;
    }
}
