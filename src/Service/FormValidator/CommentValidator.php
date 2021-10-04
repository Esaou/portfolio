<?php

declare(strict_types=1);

namespace App\Service\FormValidator;

use App\Service\Http\Session\Session;

class CommentValidator extends AbstractValidator
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


        if (!$this->testStringLength($data['comment'], 1, 3000, 'commentaire')) {
            $isValid = false;
        }

        return $isValid;
    }
}
