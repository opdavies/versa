<?php

declare(strict_types=1);

namespace App\Action;

use App\Enum\PackageManager;
use App\Enum\ProjectLanguage;
use Symfony\Component\Filesystem\Filesystem;

final class DeterminePackageManager
{
    public function __construct(
        private Filesystem $filesystem,
        private string $projectLanguage,
        private string $workingDir = '.',
    ) {
    }

    public function getPackageManager(): string
    {
        if ($this->projectLanguage === ProjectLanguage::JavaScript->value) {
            if ($this->filesystem->exists($this->workingDir.'/pnpm-lock.yaml')) {
                return PackageManager::pnpm->value;
            }

            if ($this->filesystem->exists($this->workingDir.'/yarn.lock')) {
                return PackageManager::yarn->value;
            }

            return PackageManager::npm->value;
        }

        // TODO: throw an Exception if the language cannot be determined instead of returning a default.
        return PackageManager::Composer->value;
    }
}
