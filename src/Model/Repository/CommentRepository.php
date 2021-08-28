<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Service\Database;
use App\Model\Entity\Comment;
use App\Model\Entity\Interfaces\EntityObjectInterface;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;

final class CommentRepository implements EntityRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function find(int $id): ?Comment
    {
        return null;
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Comment
    {
        return null;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $this->database->prepare('select * from comment where idPost=:idPost');
        $data = $this->database->execute($criteria);

        if ($data === null) {
            return null;
        }

        // réfléchir à l'hydratation des entités;
        $comments = [];
        foreach ($data as $comment) {
            $comments[] = new Comment((int)$comment['id'], $comment['pseudo'], $comment['text'], (int)$comment['idPost']);
        }

        return $comments;
    }

    public function findAll(): ?array
    {
        return null;
    }

    public function create(object $comment): bool
    {
        return false ;
    }

    public function update(object $comment): bool
    {
        return false;
    }

    public function delete(object $comment): bool
    {
        return false;
    }
}
