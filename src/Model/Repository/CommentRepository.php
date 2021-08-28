<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\Post;
use App\Service\Database;
use App\Model\Entity\Comment;
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
        $data = $this->database->query("select * from user where id=$id");
        $data = current($data);

        if ($data === false) {
            return null;
        }

        return new Comment((int)$data['id'], $data['content'],(int)$data['post_id'],(int)$data['user_id']);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Comment
    {
        $data = $this->findBy($criteria,$orderBy);
        $data = current($data);

        return $data === false ? null : new Comment((int)$data['id'], $data['content'],(int)$data['post_id'],(int)$data['user_id']);
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $where = $this->database->setCondition($criteria);

        if ($orderBy == null){
            $orderBy = "id desc";
        }else{
            $orderBy = $this->database->setOrderBy($orderBy);
        }

        if ($limit == null){
            $limit = 1000000;
        }
        if ($offset == null){
            $offset = 0;
        }


        $data = $this->database->prepare("select * from comment where $where order by $orderBy limit $limit offset $offset",$criteria);

        $data = json_decode(json_encode($data), true);
        return $data === null ? null : $data;
    }

    public function findAll(): ?array
    {
        $data = $this->database->query('select * from comment');

        if (empty($data)) {
            return null;
        }

        $comments = [];
        foreach ($data as $comment) {
            $comments[] = new Post((int)$comment->id, $comment->content,$comment->post_id);
        }


        return $comments;
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
