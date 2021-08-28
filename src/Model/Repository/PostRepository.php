<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\Post;
use App\Service\Database;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;

final class PostRepository implements EntityRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function find(int $id): ?Post
    {
        $data = $this->database->query("select * from post where id=$id");
        $data = current($data);

        if ($data === false) {
            return null;
        }

        return new Post((int)$data->id, $data->title, $data->content);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Post
    {

        $data = $this->findBy($criteria,$orderBy);
        $data = current($data);

        return $data === false ? null : new Post((int)$data['id'], $data['title'], $data['content']);
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


        $data = $this->database->prepare("select * from post where $where order by $orderBy limit $limit offset $offset",$criteria);

        $data = json_decode(json_encode($data), true);

        return $data === null ? null : $data;
    }

    public function findAll(): ?array
    {
        $data = $this->database->query('select * from post');

        if (empty($data)) {
            return null;
        }
        $posts = [];
        foreach ($data as $post) {
            $posts[] = new Post((int)$post->id, $post->title, $post->content);
        }

        return $posts;
    }

    public function create(object $post): bool
    {
        return false;
    }

    public function update(object $post): bool
    {
        return false;
    }

    public function delete(object $post): bool
    {
        return false;
    }
}
