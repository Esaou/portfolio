<?php

declare(strict_types=1);

namespace App\Model\Entity;

final class User
{
    public int $id_utilisateur;
    public string $email;
    public string $firstname;
    public string $lastname;
    public string $password;

    public function __construct(int $id_utilisateur, string $firstname,string $lastname, string $email, string $password)
    {
        $this->id_utilisateur = $id_utilisateur;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->password = $password;
    }

    public function getIdUtilisateur(): int
    {
        return $this->id_utilisateur;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function __toString(){

        return $this->email;

    }
}
