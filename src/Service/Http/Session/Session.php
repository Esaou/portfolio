<?php

declare(strict_types=1);

namespace App\Service\Http\Session;

final class Session
{
    private SessionParametersBag $sessionParamBag; // $_SESSION

    public function __construct()
    {
        session_start();
        $this->sessionParamBag = new SessionParametersBag($_SESSION);
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function set(string $name, mixed $value): void
    {
        $this->sessionParamBag->set($name, $value);
    }

    /**
     * @param  string $name
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
        $flashes = $this->getFlashes();

        if (!isset($flashes[$type])) {
            $flashes[$type] = [];
        }

        array_push($flashes[$type], $message);

        $this->set('flashes', $flashes);
    }

    public function getFlashes(): ?array
    {
        $flashes = $this->get('flashes');
        $this->remove('flashes');

        return $flashes;
    }
}
