<?php

namespace App\Action;

interface DetermineProjectLanguageInterface
{
    /**
     * @return non-empty-string
     */
    public function getLanguage(): string;
}
