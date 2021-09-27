<?php


declare(strict_types=1);

namespace App\Service;

use App\Service\Http\Request;
use App\Service\Http\Session\Session;

class Authorization
{

    private Session $session;
    private Request $request;

    public function __construct(Session $session, Request $request)
    {

        $this->session = $session;
        $this->request = $request;
    }

    public function isLogged():bool
    {

        if ($this->session->get('user') === null) {
            return false;
        }

        return true;
    }


    public function loggedAs(string $role):bool
    {

        $user = $this->session->get('user');

        if ($user === null) {
            return false;
        }

        if ($user->getRole() == $role) {
            return true;
        }

        return false;
    }
}
