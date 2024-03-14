<?php

namespace App\Action;

use App\Enum\ProjectLanguage;
use Illuminate\Support\Collection;
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

    /**
     * Return the languages used in the project.
     *
     * @return Collection<int, ProjectLanguage>
     */
    public function getLanguages(): Collection
    {
        $languages = collect();


        if ($this->filesystem->exists($this->workingDir.'/composer.json')) {
            $languages->push(ProjectLanguage::PHP);
        }

        if ($this->filesystem->exists($this->workingDir.'/package.json')) {
            $languages->push(ProjectLanguage::JavaScript);
        }

        return $languages;
    }
}
