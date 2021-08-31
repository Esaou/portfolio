<?php

declare(strict_types=1);

namespace App\Model\Entity;

final class Comment
{
    public int $id;
    public User|null $user; // TODO l'entity User serait plus approprié
    public string $content;
    public int $post_id;
    public string $isChecked;
    public \DateTime $createdAt;

    public function __construct(int $id, string $content, int $post_id,User|null $user,string $isChecked,\DateTime $createdAt)
    {
        $this->id = $id;
        $this->user = $user;
        $this->content = $content;
        $this->post_id = $post_id;
        $this->isChecked = $isChecked;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getIsChecked(): string
    {
        return $this->isChecked;
    }

    public function setIsChecked(string $isChecked): self
    {
        $this->isChecked = $isChecked;
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getIdPost(): int
    {
        return $this->post_id;
    }

    public function setIdPost(int $post_id): int
    {
        $this->post_id = $post_id;
        return $this->post_id;
    }

    public function __toString(){

        return $this->content;

    }
}
