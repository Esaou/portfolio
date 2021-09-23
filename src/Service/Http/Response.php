<?php

declare(strict_types=1);

namespace App\Service\Http;

class Response
{
    private string $content;
    private int $status;
    private array $headers;

    function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function send(): void
    {
        $this->setStatus($this->status);
        echo $this->content;
    }

    public function setStatus(int $status):void
    {
        http_response_code($status);
    }
}
