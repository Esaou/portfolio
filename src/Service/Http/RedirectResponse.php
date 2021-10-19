<?php

declare(strict_types=1);

namespace App\Service\Http;

class RedirectResponse extends Response
{
    private int $status;
    private array $headers;

    public function __construct(int $status = 302, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->status = $status;
        $this->headers = $headers;
    }

    public function redirect(string $action): void
    {
        $this->setStatus($this->status);
        header('Location: '.$action);
        exit();
    }
}
