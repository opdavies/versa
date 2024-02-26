<?php

namespace App\Tests;

use App\Action\DetermineProjectLanguage;
use App\Enum\ProjectLanguage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class ProjectLanguageTest extends TestCase
{
    /**
     * @test
     * @testdox It identifies a PHP project if it has a composer.json file
     */
    public function it_identifies_a_php_project_if_it_has_a_composer_json_file(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem
            ->method('exists')
            ->with('./composer.json')
            ->willReturn(true);

        $action = new DetermineProjectLanguage(
            filesystem: $filesystem,
        );

        self::assertSame(
            actual: $action->getLanguage(),
            expected: ProjectLanguage::PHP->value,
        );
    }

    /**
     * @test
     * @testdox It identifies a node project if it has a package.json file
     */
    public function it_identifies_a_node_project_if_it_has_a_package_json_file(): void
    {
        // self::markTestSkipped();

        $filesystem = $this->createMock(Filesystem::class);

        $filesystem
            ->method('exists')
            ->will(self::returnValueMap([
                ['./composer.json', false],
                ['./package.json', true],
            ]));

        $action = new DetermineProjectLanguage(
            filesystem: $filesystem,
        );

        self::assertSame(
            actual: $action->getLanguage(),
            expected: ProjectLanguage::JavaScript->value,
        );
    }
}
