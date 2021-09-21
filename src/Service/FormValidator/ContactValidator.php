<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\AbstractValidator;
use App\Service\Http\Session\Session;

class ContactValidator extends AbstractValidator
{
    private Session $session;

    public function __construct(Session $session)
    {

        parent::__construct($session);
        $this->session = $session;
    }

    public function homeContactValidator(array $data): bool
    {

        $error = false;

        if (!$this->validate($data)) {
            $error = true;
        }
        if ($data['content'] == '') {
            $this->session->addFlashes('danger', 'Le message ne peut être vide !');
            $error = true;
        }
        if ($error === true) {
            return false;
        }

        return true;
    }
}
