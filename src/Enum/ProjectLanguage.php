<?php

declare(strict_types=1);

namespace App\Enum;

enum ProjectLanguage: string
{
    case JavaScript = 'javascript';
    case PHP = 'php';

    /**
     * @param non-empty-string $language
     */
    public static function isValid(string $language): bool
    {
        return in_array(
            haystack: array_column(self::cases(), 'value'),
            needle: $language,
            strict: true,
        );
    }
}
