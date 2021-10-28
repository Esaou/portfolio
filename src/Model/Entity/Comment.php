<?php

declare(strict_types=1);

namespace App\Model\Entity;

final class Comment
{
    public int $id;
    public User|null $id_user;
    public string $comment;
    public Post $post_id;
    public string $isChecked;
    public \DateTime $createdDate;
    public string|null $slugComment;

    public function __construct(
        int $id,
        string $comment,
        Post $post_id,
        User|null $id_user,
        string $isChecked,
        \DateTime $createdDate,
        string|null $slugComment
    ) {
        $this->id = $id;
        $this->id_user = $id_user;
        $this->comment = $comment;
        $this->post_id = $post_id;
        $this->isChecked = $isChecked;
        $this->createdDate = $createdDate;
        $this->slugComment = $slugComment;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User|null
    {
        return $this->id_user;
    }

    public function setUser(User $id_user): self
    {
        $this->id_user = $id_user;
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
        return $this->createdDate;
    }

    public function setCreatedAt(\DateTime $createdDate): self
    {
        $this->createdDate = $createdDate;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getIdPost(): Post
    {
        return $this->post_id;
    }

    public function setIdPost(Post $post_id): Post
    {
        $this->post_id = $post_id;
        return $this->post_id;
    }

    public function getSlugComment(): string|null
    {
        return $this->slugComment;
    }

    public function setSlugComment(string|null $slugComment): self
    {
        $this->slugComment = $slugComment;
        return $this;
    }

    public function __toString(): string
    {
        return $this->comment;
    }
}
