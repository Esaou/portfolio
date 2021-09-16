<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\Validator;

class AccountValidator extends Validator
{
    private $session;

    public function __construct($session)
    {

        parent::__construct($session);
        $this->session = $session;

    }

    public function accountValidator(array $data):bool{

        $error = false;

        if (!$this->validate($data)){
            $error = true;
        }

        if ($error === true){
            return false;
        }

        return true;
    }

}