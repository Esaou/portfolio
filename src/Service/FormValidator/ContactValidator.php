<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\Http\Session\Session;

class ContactValidator extends AbstractValidator
{
    private Session $session;

    public function __construct(Session $session)
    {
        parent::__construct($session);
        $this->session = $session;
    }

    public function validate(array $data): bool
    {
        $isValid = true;

        if (!$this->isNotEmpty($data['firstname'], 'prénom')) {
            $isValid = false;
        }
        if (!$this->isNotEmpty($data['lastname'], 'nom')) {
            $isValid = false;
        }
        if (!$this->isNotEmpty($data['email'], 'email')) {
            $isValid = false;
        }
        if (!$this->isNotEmpty($data['content'], 'message')) {
            $isValid = false;
        }
        if (!$this->testString($data['firstname'], 'prénom')) {
            $isValid = false;
        }
        if (!$this->testString($data['lastname'], 'nom')) {
            $isValid = false;
        }
        if (!$this->testStringLength($data['lastname'], 1, 30, 'nom')) {
            $isValid = false;
        }
        if (!$this->testStringLength($data['firstname'], 1, 30, 'prénom')) {
            $isValid = false;
        }
        if (!$this->testValidEmail($data['email'])) {
            $isValid = false;
        }

        return $isValid;
    }
}
