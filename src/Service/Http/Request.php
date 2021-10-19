<?php

declare(strict_types=1);

namespace App\Service\Http;

final class Request
{
    private ParametersBag $query; // $_GET
    private ParametersBag $request; // $_POST
    private ParametersBag $files; // $_FILES
    private ParametersBag $server; // $_SERVER


    public function __construct()
    {
        $this->query = new ParametersBag($_GET);
        $this->request = new ParametersBag($_POST);
        $this->files = new ParametersBag($_FILES);
        $this->server = new ParametersBag($_SERVER);
    }

    public function query(): ParametersBag
    {
        return $this->query;
    }

    public function request(): ParametersBag
    {
        return $this->request;
    }

    public function files(): ParametersBag
    {
        return $this->files;
    }

    public function server(): ParametersBag
    {
        return $this->server;
    }

    public function getMethod(): string
    {
        return $this->server->get('REQUEST_METHOD');
    }
}
