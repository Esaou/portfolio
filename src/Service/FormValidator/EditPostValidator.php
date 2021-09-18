<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\AbstractValidator;

class EditPostValidator extends AbstractValidator
{
    private $session;

    public function __construct($session)
    {

        parent::__construct($session);
        $this->session = $session;

    }

    public function editPostValidator(array $data):bool{

        $error = false;

        if (!$this->validate($data)){
            $error = true;
        }
        if ($data['title'] == '' or $data['chapo'] == '' or $data['content'] == ''){
            $this->session->addFlashes('danger','Tous les champs doivent être remplis !');
            $error = true;
        }

        if ($error === true){
            return false;
        }

        return true;
    }

}