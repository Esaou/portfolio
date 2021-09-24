<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\AbstractValidator;
use App\Service\Http\Session\Session;

class EditPostValidator extends AbstractValidator
{
    private Session $session;

    public function __construct(Session $session)
    {

        parent::__construct($session);
        $this->session = $session;
    }

    public function validate(array $data):bool
    {

        $isValid = true;

        if (!$this->isNotEmpty($data['title'], 'titre')) {
            $isValid = false;
        }
        if (!$this->isNotEmpty($data['chapo'], 'chapo')) {
            $isValid = false;
        }
        if (!$this->isNotEmpty($data['content'], 'contenu')) {
            $isValid = false;
        }

        return $isValid;
    }
}
