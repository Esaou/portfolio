<?php

declare(strict_types=1);

namespace App\Model\Entity;

use DateTime;

final class Post
{
    public int $id_post;
    public string $title;
    public string $content;
    public string $chapo;
    public \DateTime $createdAt;
    public \DateTime|null $updatedAt;
    public User|null $user;
    public string $slugPost;

    public function __construct(
        int $id_post,
        string $chapo,
        string $title,
        string $content,
        Datetime $createdAt,
        Datetime|null $updatedAt,
        User|null $user,
        string $slugPost
    ) {
        $this->id_post = $id_post;
        $this->title = $title;
        $this->content = $content;
        $this->chapo = $chapo;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->user = $user;
        $this->slugPost = $slugPost;
    }

    public function getIdPost(): int
    {
        return $this->id_post;
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

    public function getUpdatedAt(): \DateTime|null
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime|null $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): User|null
    {
        return $this->user;
    }

    public function setUser(User|null $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getSlugPost(): string
    {
        return $this->slugPost;
    }

    public function setSlugPost(string $slugPost): self
    {
        $this->slugPost = $slugPost;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title;
    }


}
