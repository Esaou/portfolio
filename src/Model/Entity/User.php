<?php

declare(strict_types=1);

namespace App\Model\Entity;

final class User
{
    public int|null $id_utilisateur;
    public string|null $email;
    public string|null $firstname;
    public string|null $lastname;
    public string|null $password;
    public string|null $isValid;
    public string|null $role;
    public string|null $token;
    public string|null $slugUser;

    public function __construct(
        int|null $id_utilisateur,
        string|null $firstname,
        string|null $lastname,
        string|null $email,
        string|null $password,
        string|null $isValid,
        string|null $role,
        string|null $token,
        string|null $slugUser
    ) {
        $this->id_utilisateur = $id_utilisateur;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->isValid = $isValid;
        $this->token = $token;
        $this->slugUser = $slugUser;
    }

    public function getIdUtilisateur(): int|null
    {
        return $this->id_utilisateur;
    }

    public function getFirstname(): string|null
    {
        return $this->firstname;
    }

    public function setFirstname(string|null $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): string|null
    {
        return $this->lastname;
    }

    public function setLastname(string|null $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getEmail(): string|null
    {
        return $this->email;
    }

    public function setEmail(string|null $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string|null
    {
        return $this->password;
    }

    public function setPassword(string|null $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): string|null
    {
        return $this->role;
    }

    public function setRole(string|null $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getIsValid(): string
    {
        return $this->isValid;
    }

    public function setIsValid(string|null $isValid): self
    {
        $this->isValid = $isValid;
        return $this;
    }

    public function getToken(): string|null
    {
        return $this->token;
    }

    public function setToken(string|null $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getSlugUser(): string|null
    {
        return $this->slugUser;
    }

    public function setSlugUser(string|null $slugUser): self
    {
        $this->slugUser = $slugUser;
        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
