<?php

declare(strict_types=1);

namespace App\Service;

use Dotenv\Dotenv;

class Environment
{

    private array $environment;

    public function __construct()
    {

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->environment = $_ENV;
    }

    public function getDbName(): string|null
    {

        if (!isset($this->environment['DB_NAME'])) {
            return null;
        }
        return $this->environment['DB_NAME'];
    }

    public function getDbHost(): string|null
    {

        if (!isset($this->environment['DB_HOST'])) {
            return null;
        }
        return $this->environment['DB_HOST'];
    }

    public function getDbUser(): string|null
    {

        if (!isset($this->environment['DB_USER'])) {
            return null;
        }
        return $this->environment['DB_USER'];
    }

    public function getDbPass(): string|null
    {

        if (!isset($this->environment['DB_PASS'])) {
            return null;
        }
        return $this->environment['DB_PASS'];
    }

    public function getAppEnv(): string|null
    {

        if (!isset($this->environment['APP_ENV'])) {
            return null;
        }
        return $this->environment['APP_ENV'];
    }
}
