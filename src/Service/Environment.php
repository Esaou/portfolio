<?php

declare(strict_types=1);

namespace App\Service;

use Dotenv\Dotenv;

class Environment
{

    private array $environment;

    public function __construct(){

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->environment = $_ENV;

    }

    public function getDbName()
    {

        return $this->environment['DB_NAME'];

    }

    public function getDbHost()
    {

        return $this->environment['DB_HOST'];

    }

    public function getDbUser()
    {

        return $this->environment['DB_USER'];

    }

    public function getDbPass()
    {

        return $this->environment['DB_PASS'];

    }

    public function getAppEnv()
    {

        return $this->environment['APP_ENV'];

    }

}