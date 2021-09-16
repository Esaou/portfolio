<?php

declare(strict_types=1);

namespace App\Service;

class Validator
{

    private $session;

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function validate(array $data):bool
    {

        $error = false;

        if ($data['tokenPost'] != $data['tokenSession']){
            $this->session->addFlashes('danger','Token de session expiré !');
            $error = true;
        }

        if ((isset($data['lastname']) and $data['lastname'] == '')
            or (isset($data['firstname']) and $data['firstname'] == '')
            or (isset($data['email']) and $data['email'] == '')
            or (isset($data['password']) and $data['password'] == '')) {

            $this->session->addFlashes('danger', 'Tous les champs doivent être remplis !');
            $error = true;

        }

        if (isset($data['lastname']) and (strlen($data['lastname']) < 1
                or strlen($data['lastname']) > 30
                or !preg_match('#[^0-9]#',$data['lastname'])
                or preg_match('#[\W]#',$data['lastname']))) {

            $this->session->addFlashes('danger', 'Le nom peut contenir de 2 à 30 caractères sans chiffres ni caractères spéciaux !');
            $error = true;

        }

        if (isset($data['firstname']) and (strlen($data['firstname']) < 1
                or strlen($data['firstname']) > 30
                or !preg_match('#[^0-9]#',$data['firstname'])
                or preg_match('#[\W]#',$data['firstname']))) {

            $this->session->addFlashes('danger', 'Le prénom peut contenir de 2 à 30 caractères sans chiffres ni caractères spéciaux !');
            $error = true;

        }

        if (isset($data['email']) and !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {

            $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');
            $error = true;

        }

        if (isset($data['passwordConfirm']) and $data['passwordConfirm'] !== $data['password']){

            $this->session->addFlashes('danger', 'Mots de passe non identiques !');
            $error = true;

        }

        if ($error === true){
            return false;
        }

        return true;
    }


}