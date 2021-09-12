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

    public function registerValidator($nom,$prenom,$email,$validEmail,$password,$confirmPassword,$tokenPost,$tokenSession):bool{

        if (empty($nom) or empty($prenom) or empty($email) or empty($password)) {

            $this->session->addFlashes('danger', 'Tous les champs doivent être remplis !');
            return false;

        } elseif ($tokenPost != $tokenSession){
            $this->session->addFlashes('danger','Token de session expiré !');
            return false;
        }elseif ($confirmPassword !== $password){

            $this->session->addFlashes('danger', 'Mots de passe non identiques !');
            return false;

        } elseif (strlen($prenom) < 2 or strlen($prenom) > 30 or strlen($nom) < 2 or strlen($nom) > 30) {

            $this->session->addFlashes('danger', 'Le prénom et le nom doivent contenir de 2 à 30 caractères !');
            return false;

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');
            return false;

        } elseif ($validEmail !== null) {

            $this->session->addFlashes('danger', 'L\'email renseigné est déjà utilisé !');
            return false;

        }

        return true;

    }

}