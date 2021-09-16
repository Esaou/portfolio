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
        // TODO Il faut renvoyer aussi le status de la réponse
        $this->setStatus($this->status);
        echo $this->content;

    }

    public function setStatus(int $status){
        http_response_code($status);
    }

}
