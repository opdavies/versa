<?php

namespace App\Action;

use App\Enum\ProjectLanguage;
use Symfony\Component\Filesystem\Filesystem;

final class DetermineProjectLanguage implements DetermineProjectLanguageInterface
{
    public function __construct(
        private Filesystem $filesystem,
        private string $workingDir = '.',
    ) {
    }

    public function getLanguage(): string
    {
        if ($this->filesystem->exists($this->workingDir.'/composer.json')) {
            return ProjectLanguage::PHP->value;
        }

        if ($this->filesystem->exists($this->workingDir.'/package.json')) {
            return ProjectLanguage::JavaScript->value;
        }

        // TODO: What to do if a project contains multiple languages?
        // e.g. a composer.lock file (PHP) and pnpm-lock.yaml file (JS)?

        // TODO: validate the language is an allowed value.

        return ProjectLanguage::PHP->value;
    }
}
