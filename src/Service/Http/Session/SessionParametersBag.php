<?php

declare(strict_types=1);

namespace App\Service\Http\Session;

use App\Service\Http\ParametersBag;

final class SessionParametersBag extends ParametersBag
{

    function __construct(array &$parameters)
    {
        parent::__construct($parameters);
    }

    function unset(string $key): void
    {
        unset($this->parameters[$key]);
    }
}
