<?php

namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

abstract class AbstractCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption(
            name: 'extra-args',
            shortcut: 'a',
            mode: InputArgument::OPTIONAL,
            description: 'Any additonal arguments to pass to the command.',
        );

        $this->addOption(
            name: 'language',
            shortcut: 'l',
            mode: InputArgument::OPTIONAL,
            description: 'The project language',
            suggestedValues: ['php', 'javascript'],
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
}
