<?php

namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractCommand extends Command
{
    public function __construct(
        protected Filesystem $filesystem,
        ?string $name = null,
    ) {
        parent::__construct(name: $name);
    }

    protected function configure(): void
    {
        // Allow for passing arbitrary arguments to the underlying command, for
        // example, to run `composer install` with the `--no-dev` option:
        //
        // ./bin/console install -- --no-dev
        //
        // And to set the port and environment for a Sculpin project, multiple
        // arguments can be passed at once as a string:
        //
        // ./bin/console run -- '-e prod --port 8001'
        $this->addArgument('*');

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
