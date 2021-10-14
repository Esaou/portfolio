<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Model\Entity\User;
use App\Service\Http\Session\Session;

abstract class AbstractValidator
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function testValidEmail(string $email): bool
    {
        $isValid = true;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $isValid = false;
            $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');
        }

        return $isValid;
    }

    public function isNotEmpty(string $string, string $fieldName): bool
    {
        $isValid = true;

        if ($string === '') {
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ ' . $fieldName . ' doit être rempli !'
            );
        }

        return $isValid;
    }

    public function testString(string $string, string $fieldName): bool
    {
        $isValid = true;


        if (preg_match('#[0-9]#', $string)) {
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ ' . $fieldName . ' ne peut pas contenir de chiffres !'
            );
        }

        if (preg_match('~[^\\pL\d\s-]+~u', $string)) {
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ ' . $fieldName . ' ne peut pas contenir de caractères spéciaux !'
            );
        }

        if (substr_count($string, '-') > 2 || substr_count($string, ' ') > 2) {
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ ' . $fieldName . ' ne peut pas contenir que deux tirets ou espaces !'
            );
        }

        return $isValid;
    }

    public function testStringLength(string $string, int $min, int $max, string $fieldName): bool
    {
        $isValid = true;

        if (strlen($string) < $min || strlen($string) > $max) {
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ ' . $fieldName . ' doit contenir entre ' . $min . ' et ' . $max . ' caractères !'
            );
        }

        return $isValid;
    }

    public function testPassword(string $password): bool
    {
        $isValid = true;

        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[-+!*$@%_])([-+!*$@%_\w]{8,100})$/', $password)) {
            $isValid = false;
            $this->session->addFlashes(
                'danger',
                'Le champ mot de passe doit contenir au moins 1 chiffre,
                 une lettre minuscule, majuscule, un caractère spécial et 8 caractères minimum !'
            );
        }

        return $isValid;
    }

    public function isNotUsedEmail(User|null $email): bool
    {
        $isValid = true;

        if ($email !== null) {
            $isValid = false;
            $this->session->addFlashes('danger', 'L\'email renseigné est déjà utilisé !');
        }

        return $isValid;
    }

    public function testPasswordConfirm(string $password, string $passConfirm): bool
    {
        $isValid = true;

        if ($passConfirm !== $password) {
            $isValid = false;
            $this->session->addFlashes('danger', 'Mots de passe non identiques !');
        }

        return $isValid;
    }

    public function isUserPasswordValid(User|null $user, string $password): bool
    {
        $isValid = true;

        if ($user === null || !password_verify($password, $user->getPassword())) {
            $this->session->addFlashes('danger', 'Mauvais identifiants');
            $isValid = false;
        }

        return $isValid;
    }

    public function isUserValid(User|null $user): bool
    {
        $isValid = true;

        if ($user !== null && $user->getIsValid() === 'Non') {
            $this->session->addFlashes('danger', 'Compte non valide !');
            $isValid = false;
        }

        return $isValid;
    }
}
