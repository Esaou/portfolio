<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\Validator;

class RegisterValidator extends Validator
{
    private $session;

    public function __construct($session)
    {

        parent::__construct($session);
        $this->session = $session;

    }

    public function registerValidator(array $data):bool{

        $error = false;

        if (!$this->validate($data)){
            $error = true;
        }

        if ($data['validEmail'] !== null) {

            $this->session->addFlashes('danger', 'L\'email renseigné est déjà utilisé !');
            $error = true;

        }

        if ($error === true){
            return false;
        }

        return true;
    }

}