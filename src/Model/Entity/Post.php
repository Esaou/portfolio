<?php

declare(strict_types=1);

namespace App\Model\Entity;

use DateTime;

final class Post
{
    public int $id;
    public string $title;
    public string $content;
    public string $chapo;
    public \DateTime $createdAt;
    public \DateTime $updatedAt;
    public User|null $user;

    public function __construct(int $id,string $chapo,string $title, string $content,Datetime $createdAt,Datetime $updatedAt,User|null $user)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->chapo = $chapo;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->user = $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getChapo(): string
    {
        return $this->chapo;
    }

    public function setChapo(string $chapo): self
    {
        $this->chapo = $chapo;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): User|null
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function __toString(){

        return $this->title;

    }
}
