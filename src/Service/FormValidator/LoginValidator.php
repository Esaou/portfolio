<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\AbstractValidator;
use App\Service\Http\Session\Session;

class LoginValidator extends AbstractValidator
{
    private Session $session;

    public function __construct(Session $session)
    {

        parent::__construct($session);
        $this->session = $session;
    }

    public function loginValidator(array $data):bool
    {

        $error = false;

        if (!$this->validate($data)) {
            $error = true;
        }

        if ($data == null) {
            $this->session->addFlashes('danger', 'Aucun identifiant renseigné !');
            $error = true;
        }

        if ($data['user'] == null) {
            $this->session->addFlashes('danger', 'Mauvais identifiants');
            $error = true;
        }

        if ($data['user']->getIsValid() == 'Non') {
            $this->session->addFlashes('danger', 'Compte non valide !');
            $error = true;
        }

        if (!password_verify($data['password'], $data['user']->getPassword())) {
            $this->session->addFlashes('danger', 'Mauvais identifiants');
            $error = true;
        }

        if ($error == true) {
            return false;
        }

        return true;
    }
}
