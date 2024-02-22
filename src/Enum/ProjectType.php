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
}
