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
        $data = $this->database->query("select * from post left join user on post.user_id = user.id_utilisateur where id=$id");
        $data = current($data);

        if ($data === false) {
            return null;
        }

        $data->createdAt = new \DateTime($data->createdAt);
        $data->updatedAt = new \DateTime($data->updatedAt);
        $user = new User((int)$data->id_utilisateur, $data->firstname,$data->lastname, $data->email, $data->password);

        return new Post((int)$data->id,$data->chapo, $data->title, $data->content,$data->createdAt,$data->updatedAt,$user);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Post
    {

        $data = $this->findBy($criteria,$orderBy);
        $data = current($data);

        $user = new User((int)$data->user->id_utilisateur, $data->user->firstname,$data->user->lastname, $data->user->email, $data->user->password);

        return $data === false ? null : new Post((int)$data->id,$data->chapo, $data->title, $data->content,$data->createdAt,$data->updatedAt,$user);
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


        $data = $this->database->prepare("select * from post left join user on post.user_id = user.id_utilisateur where $where order by $orderBy limit $limit offset $offset",$criteria);

        $data = json_decode(json_encode($data), true);

        if (empty($data)) {
            return null;
        }

        $posts = [];

        foreach ($data as $post) {
            $post['createdAt'] = new \DateTime($post['createdAt']);
            $post['updatedAt'] = new \DateTime($post['updatedAt']);
            $user = new User((int)$post['id_utilisateur'], $post['firstname'],$post['lastname'], $post['email'], $post['password']);
            $posts[] = new Post((int)$post['id'],$post['chapo'], $post['title'], $post['content'],$post['createdAt'],$post['updatedAt'],$user);
        }

        return $posts;
    }

    public function findAll(): ?array
    {
        $data = $this->database->query('select * from post left join user on post.user_id = user.id_utilisateur');

        if (empty($data)) {
            return null;
        }
        $posts = [];
        foreach ($data as $post) {
            $post->createdAt = new \DateTime($post->createdAt);
            $post->updatedAt = new \DateTime($post->updatedAt);
            $user = new User((int)$post->id_utilisateur, $post->firstname,$post->lastname, $post->email, $post->password);
            $posts[] = new Post((int)$post->id,$post->chapo, $post->title, $post->content,$post->createdAt,$post->updatedAt,$user);
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
