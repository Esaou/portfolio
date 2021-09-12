<?php


namespace App\Service;


use App\Service\Http\Request;
use App\Service\Http\Session\Session;

class Authorization
{

    private Session $session;
    private Request $request;

    public function __construct(Session $session,Request $request)
    {

        $this->session = $session;
        $this->request = $request;
    }

    public function notLogged():bool
    {
        return is_null($this->session->get('user'));
    }

    public function loggedAs(string $role):bool
    {
        $user = $this->session->get('user');

        if ($user->getRole() == $role){
            return true;
        }
        return false;

    }

}