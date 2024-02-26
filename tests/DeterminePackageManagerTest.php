<?php

declare(strict_types=1);

namespace App\Tests;

use App\Action\DeterminePackageManager;
use App\Action\DetermineProjectLanguage;
use App\Action\DetermineProjectLanguageInterface;
use App\Enum\PackageManager;
use App\Enum\ProjectLanguage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class DeterminePackageManagerTest extends TestCase
{
    /** @test */
    public function it_finds_php(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->method('exists')
            ->with('./composer.json')
            ->willReturn(true);

        $action = new DeterminePackageManager(
            filesystem: $filesystem,
            projectLanguage: ProjectLanguage::PHP->value,
        );

        self::assertSame(
            actual: $action->getPackageManager(),
            expected: PackageManager::Composer->value,
        );
    }

    public function lockFileProvider(): array
    {
        return [
            'npm' => [
                './package-lock.json',
                PackageManager::npm->value,
                [
                    ['./package-lock.json', true],
                    ['./pnpm-lock.yaml', false],
                    ['./yarn.lock', false],
                ],
            ],
            'pnpm' => [
                './pnpm-lock.yaml',
                PackageManager::pnpm->value,
                [
                    ['./package-lock.json', false],
                    ['./pnpm-lock.yaml', true],
                    ['./yarn.lock', false],
                ],
            ],
            'yarn' => [
                './yarn.lock',
                PackageManager::yarn->value,
                [
                    ['./package-lock.json', false],
                    ['./pnpm-lock.yaml', false],
                    ['./yarn.lock', true],
                ],
            ],
        ];
    }

    /**
     * @dataProvider lockFileProvider
     * @test
     */
    public function it_finds_node(
        string $lockFile,
        string $expectedPackageManager,
        array $valueMap,
    ): void {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->method('exists')
            ->will(self::returnValueMap($valueMap));

        $action = new DeterminePackageManager(
            filesystem: $filesystem,
            projectLanguage: ProjectLanguage::JavaScript->value,
        );

        self::assertSame(
            actual: $action->getPackageManager(),
            expected: $expectedPackageManager,
        );
    }
}
