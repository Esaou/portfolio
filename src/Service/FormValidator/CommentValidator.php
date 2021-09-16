<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\Validator;

class CommentValidator extends Validator
{
    private $session;

    public function __construct($session)
    {

        parent::__construct($session);
        $this->session = $session;

    }

    public function commentValidator(array $data):bool{

        $error = false;

        if (!$this->validate($data)){
            $error = true;
        }

        if(strlen($data['comment']) < 1 or strlen($data['comment']) > 5000){
            $this->session->addFlashes('danger','Votre commentaire peut contenir de 1 à 5000 caractères');
            return false;
        }

        if ($error === true){
            return false;
        }

        return true;
    }

}