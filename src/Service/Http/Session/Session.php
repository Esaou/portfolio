<?php

declare(strict_types=1);

namespace App\Service\Http\Session;

final class Session
{
    private SessionParametersBag $sessionParamBag; // $_SESSION

    function __construct()
    {

        session_start();
        $this->sessionParamBag = new SessionParametersBag($_SESSION);
    }

    /**
     * @param mixed $value
     */
    public function set(string $name,mixed $value): void
    {
        $this->sessionParamBag->set($name, $value);
    }

    /**
    * @return mixed
    */
    public function get(string $name): mixed
    {
        return $this->sessionParamBag->get($name);
    }

    public function toArray(): ?array
    {
        return $this->sessionParamBag->all();
    }

    public function remove(string $name): void
    {
        $this->sessionParamBag->unset($name);
    }

    public function addFlashes(string $type, string $message): void
    {
        $this->set('flashes', [$type => $message]);
    }

    public function getFlashes(): ?array
    {
        $flashes = $this->get('flashes');
        $this->remove('flashes');

        return $flashes;
    }
}
