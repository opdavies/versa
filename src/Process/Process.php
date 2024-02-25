<?php

declare(strict_types=1);

namespace App\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * Create a new Process instance with some extra options.
 */
final class Process
{
    /**
     * @param non-empty-array<int, non-empty-string> $command
     * @param string $workingDir
     * @param string[] $args
     */
    public static function create(array $command, string $workingDir, array $args = []): SymfonyProcess
    {
        $process = new SymfonyProcess(command: array_filter([...$command, ...$args]));
        $process->setTty(true);
        $process->setWorkingDirectory($workingDir);

        return $process;
    }
}
