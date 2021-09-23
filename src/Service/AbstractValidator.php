<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Http\Session\Session;

abstract class AbstractValidator
{

    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function validate(array $data):bool
    {

        $error = false;

        if ((isset($data['lastname']) && $data['lastname'] == '')
            || (isset($data['firstname']) && $data['firstname'] == '')
            || (isset($data['email']) && $data['email'] == '')
            || (isset($data['password']) && $data['password'] == '')) {
            $this->session->addFlashes('danger', 'Tous les champs doivent être remplis !');
            $error = true;
        }

        if (isset($data['lastname']) && (strlen($data['lastname']) < 1
                || strlen($data['lastname']) > 30
                || !preg_match('#[^0-9]#', $data['lastname'])
                || preg_match('~[^\\pL\d]+~u', $data['lastname']))) {
            $this->session->addFlashes(
                'danger',
                'Le nom peut contenir de 2 à 30 caractères sans chiffres ni caractères spéciaux !'
            );
            $error = true;
        }

        if (isset($data['firstname']) && (strlen($data['firstname']) < 1
                || strlen($data['firstname']) > 30
                || !preg_match('#[^0-9]#', $data['firstname'])
                || preg_match('~[^\\pL\d]+~u', $data['firstname']))) {
            $this->session->addFlashes(
                'danger',
                'Le champ prénom peut contenir de 2 à 30 caractères sans chiffres ni caractères spéciaux !'
            );
            $error = true;
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');
            $error = true;
        }

        if (isset($data['passwordConfirm']) && $data['passwordConfirm'] !== $data['password']) {
            $this->session->addFlashes('danger', 'Mots de passe non identiques !');
            $error = true;
        }

        if ($error === true) {
            return false;
        }

        return true;
    }

    public function testValidEmail(string $email):bool
    {
        $isValid = true;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isValid = false;
            $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');
        }

        return $isValid;
    }

    public function isNotEmpty(string $string, string $fieldName):bool
    {
        $isValid = true;

        if ($string === ''){
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ '. $fieldName .' doit être rempli !'
            );
        }

        return $isValid;
    }

    public function testString(string $string,string $fieldName):bool
    {

        $isValid = true;

        if (!preg_match('#[^0-9]#', $string)){
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ '. $fieldName .' ne peut pas contenir de chiffres !'
            );
        }

        if (preg_match('~[^\\pL\d]+~u', $string)){
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ '. $fieldName .' ne peut pas contenir de caractères spéciaux !'
            );
        }

        return $isValid;
    }

    public function testStringLength(string $string,int $min,int $max, string $fieldName):bool
    {

        $isValid = true;

        if (strlen($string) < $min || strlen($string) > $max){
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ '. $fieldName .' doit contenir entre '.$min.' et '.$max.' caractères !'
            );
        }

        return $isValid;
    }

}
