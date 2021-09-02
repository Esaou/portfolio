<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\Post;
use App\Model\Entity\User;
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
        $data = $this->findBy(['id'=>$id]);

        if (!empty($data)){
            $data = current($data);
        }

        if ($data === false) {
            return null;
        }

        return $data;
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Post
    {

        $user = null;
        $data = $this->findBy($criteria,$orderBy);

        if (!is_null($data)){
            $data = current($data);
        }

        return $data === null ? null : $data;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $sql = "select * from post inner join user on post.user_id = user.id_utilisateur ";

        if (!empty($criteria)){
            $sql .= $this->database->setCondition($criteria);
        }

        if (!is_null($orderBy)){
            $sql .= ' order by '.$this->database->setOrderBy($orderBy);
        }

        if (!is_null($limit)){
            $sql .= ' limit '.$limit;
        }

        if (!is_null($offset)){
            $sql .= ' offset '.$offset;
        }

        $data = $this->database->prepare($sql,$criteria);

        if (empty($data)) {
            return null;
        }

        $posts = [];

        foreach ($data as $post) {
            $post->createdAt = new \DateTime($post->createdAt);
            if (!is_null($post->updatedAt)){
                $post->updatedAt = new \DateTime($post->updatedAt);
            }
            $user = new User((int)$post->id_utilisateur, $post->firstname,$post->lastname, $post->email, $post->password,$post->isValid,$post->role,$post->token);
            $posts[] = new Post((int)$post->id,$post->chapo, $post->title, $post->content,$post->createdAt,$post->updatedAt,$user);
        }

        return $posts;
    }

    public function findAll(): ?array
    {
        $data = $this->findBy([]);

        if (empty($data)) {
            return null;
        }

        return $data;
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

    public function previousPost(int $id): int|null
    {
        $last = $this->database->query("SELECT * FROM post ORDER BY id DESC LIMIT 0 ");
        $nextPost = $this->database->query("SELECT * FROM post WHERE id>$id LIMIT 0,1 ");

        if($nextPost !== $last){

            $nextPost = current($nextPost)->id;
            return (int)$nextPost;

        }else{

            return null;

        }
    }

    public function nextPost(int $id): int|null
    {
        $previousPost = $this->database->query("SELECT * FROM post WHERE id<$id ORDER BY id DESC LIMIT 0,1 ");

        if(!empty($previousPost)){

            $previousPost = current($previousPost)->id;
            return (int)$previousPost;

        }else{

            return null;

        }
    }

    public function countAllPosts():int
    {

        $data = $this->database->query("SELECT COUNT(*) AS nb FROM post ORDER BY id desc ");
        $data = current($data);

        return (int)$data->nb;
    }
}
