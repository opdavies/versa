<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectType: string
{
    // JavaScript.
    case Fractal = 'fractal';

    // PHP.
    case Drupal = 'drupal';
    case Sculpin = 'sculpin';
    case Symfony = 'symfony';

    /**
     * @param non-empty-string $projectType
     */
    public static function isValid(string $projectType): bool
    {
        return in_array(
            haystack: array_column(self::cases(), 'value'),
            needle: $projectType,
            strict: true,
        );
    }
}
