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

    public function homeContactValidator(array $data): bool
    {

        if ($data['tokenPost'] != $data['tokenSession']){
        $this->session->addFlashes('danger','Token de session expiré !');
            return false;
        }elseif ($data['lastname'] == '' or $data['firstname'] == '' or $data['email'] == ''or $data['content'] == ''){

            $this->session->addFlashes('danger','Tous les champs doivent être remplis !');
            return false;

        }elseif (strlen($data['firstname']) < 2 or strlen($data['firstname']) > 30 or strlen($data['lastname']) < 2 or strlen($data['lastname']) > 30){

            $this->session->addFlashes('danger','Le prénom et le nom doivent contenir de 2 à 30 caractères !');
            return false;

        }elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)){

            $this->session->addFlashes('danger','L\'email renseigné n\'est pas valide !');
            return false;

        }

        return true;

    }

    public function registerValidator(array $data):bool{


        if ($data['lastname'] == '' or $data['firstname'] == '' or $data['email'] == '' or $data['password'] == '') {

            $this->session->addFlashes('danger', 'Tous les champs doivent être remplis !');
            return false;

        } elseif ($data['tokenPost'] != $data['tokenSession']){
            $this->session->addFlashes('danger','Token de session expiré !');
            return false;
        }elseif ($data['password'] != $data['passwordConfirm']){
            $this->session->addFlashes('danger', 'Mots de passe non identiques !');
            return false;

        } elseif (strlen($data['firstname']) < 2 or strlen($data['firstname']) > 30 or strlen($data['lastname']) < 2 or strlen($data['lastname']) > 30) {

            $this->session->addFlashes('danger', 'Le prénom et le nom doivent contenir de 2 à 30 caractères !');
            return false;

        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {

            $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');
            return false;

        } elseif ($data['validEmail'] !== null) {

            $this->session->addFlashes('danger', 'L\'email renseigné est déjà utilisé !');
            return false;

        }

        return true;

    }

    public function commentValidator(array $data):bool{

        if ($data['tokenPost'] != $data['tokenSession']){
            $this->session->addFlashes('danger','Token de session expiré !');
            return false;
        }elseif(strlen($data['comment']) < 1 or strlen($data['comment']) > 5000){
            $this->session->addFlashes('danger','Votre commentaire ne peut excéder 5000 caractères');
            return false;
        }

        return true;

    }

}