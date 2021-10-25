<?php

declare(strict_types=1);

namespace App\Service;

class Environment
{

    private array $environment;

    public function __construct()
    {

        if (file_exists(__DIR__. '/../../.env')) {
            $variables = file(__DIR__. '/../../.env',FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
        }

        foreach ($variables as $variable) {
            putenv($variable);
        }

        $this->environment = getenv();

    }

    public function get($key): string|null
    {

        if (!isset($this->environment[$key])) {
            return null;
        }
        return $this->environment[$key];
    }

}
