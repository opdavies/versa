<?php

declare(strict_type=1);

namespace App\Enum;

enum ProjectType: string
{
    // PHP.
    case Drupal = 'drupal';
    case Sculpin = 'sculpin';
}
