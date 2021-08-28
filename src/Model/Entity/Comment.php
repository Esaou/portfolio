<?php

declare(strict_types=1);

namespace App\Model\Entity;

final class Comment
{
    private int $id;
    private string $user; // TODO l'entity User serait plus approprié
    private string $content;
    private int $idPost;

    public function __construct(int $id, string $content, int $idPost)
    {
        $this->id = $id;
        $this->user = $user = '';
        $this->content = $content;
        $this->idPost = $idPost;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): string
    {
        return $this->user;
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
        return $this->idPost;
    }

    public function __toString(){

        return $this->content;

    }
}
