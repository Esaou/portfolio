<?php

declare(strict_types=1);

namespace App\Service\Http;

class RedirectResponse extends Response
{

    private string $url;
    private int $status;
    private array $headers;

    public function __construct(string $action, int $status = 302, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->url = $action;
        $this->status = $status;
        $this->headers = $headers;

        $this->setTargetUrl($this->url);

    }

    public function setTargetUrl(string $url):void
    {
        $this->setStatus($this->status);
        header('Location: index.php?action=' . $url);
        exit();
    }


}