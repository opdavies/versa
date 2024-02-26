<?php

namespace App\Enum;

enum PackageManager: string
{
    case Composer = 'composer';
    case npm = 'npm';
    case pnpm = 'pnpm';
    case yarn = 'yarn';
}
