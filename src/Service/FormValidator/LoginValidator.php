<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\Http\Session\Session;

class LoginValidator extends AbstractValidator
{
    private Session $session;

    public function __construct(Session $session)
    {

        parent::__construct($session);
        $this->session = $session;
    }

    public function validate(array $data):bool
    {

        $isValid = true;

        if (!$this->isUserValid($data['user'])) {
            $isValid = false;
        }
        if (!$this->isUserPasswordValid($data['user'], $data['password'])) {
            $isValid = false;
        }

        return $isValid;
    }
}
