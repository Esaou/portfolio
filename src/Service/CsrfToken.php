<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Http\Request;
use App\Service\Http\Session\Session;

class CsrfToken
{
    private Session $session;
    private Request $request;

    public function __construct(Session $session, Request $request)
    {
        $this->session = $session;
        $this->request = $request;
    }

    public function checkToken(): bool
    {
        $tokenPost = $this->request->request()->get('token');
        $tokenSession = $this->session->get('token');

        $result = true;

        if ($tokenPost !== $tokenSession) {
            $this->session->addFlashes('danger', 'Token de session expiré !');
            $result = false;
        }

        return $result;
    }

    public function newToken(): string
    {
        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);

        $this->session->set('token', $token);

        return $token;
    }
}
