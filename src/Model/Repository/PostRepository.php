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

    public function find(int $idPost): ?Post
    {
        $data = $this->findBy(['id'=>$idPost]);

        if (!empty($data)) {
            $data = current($data);
        }

        if ($data === false) {
            return null;
        }

        return $data;
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Post
    {

        $data = $this->findBy($criteria, $orderBy);

        if ($data !== null) {
            $data = current($data);
        }

        return $data === null ? null : $data;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $sql = "select * from post inner join user on post.user_id = user.id_utilisateur ";

        if (!empty($criteria)) {
            $sql .= $this->database->setCondition($criteria);
        }

        if ($orderBy !== null) {
            $sql .= ' order by '.$this->database->setOrderBy($orderBy);
        }

        if ($limit !== null) {
            $sql .= ' limit '.$limit;
        }

        if ($offset !== null) {
            $sql .= ' offset '.$offset;
        }

        $data = $this->database->prepare($sql, $criteria);

        if (empty($data)) {
            return null;
        }

        $posts = [];

        if (is_iterable($data)) {
            foreach ($data as $post) {
                $post->createdAt = new \DateTime($post->createdAt);
                if ($post->updatedAt !== null) {
                    $post->updatedAt = new \DateTime($post->updatedAt);
                }
                $user = new User(
                    (int)$post->id_utilisateur,
                    $post->firstname,
                    $post->lastname,
                    $post->email,
                    $post->password,
                    $post->isValid,
                    $post->role,
                    $post->token
                );
                $posts[] = new Post(
                    (int)$post->id_post,
                    $post->chapo,
                    $post->title,
                    $post->content,
                    $post->createdAt,
                    $post->updatedAt,
                    $user
                );
            }
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
        $criteria = [];

        $post = get_object_vars($post);

        foreach ($post as $key => $value) {
            if ($key !== 'createdAt' && $key !== 'user' && $key !== 'id_post') {
                $criteria[$key] = $value;
            } elseif ($key === 'createdAt') {
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            } elseif ($key === 'user') {
                $criteria[$key] = $value->id_utilisateur;
            }
        }


        $sql = "INSERT INTO post (title,chapo,content, createdAt,updatedAt,user_id) 
                VALUES (:title,:chapo,:content,:createdAt,:updatedAt,:user )";

        $result = $this->database->prepare($sql, $criteria);

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function update(object $post): bool
    {
        $criteria = [];

        $post = get_object_vars($post);

        foreach ($post as $key => $value) {
            if ($key === 'createdAt') {
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            } elseif ($key === 'updatedAt') {
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            } elseif ($key === 'user') {
                $criteria['user_id'] = $value->id_utilisateur;
            } else {
                $criteria[$key] = $value;
            }
        }


        $set = $this->database->setConditionUpdatePost($criteria);

        $sql = "UPDATE post SET ";

        $sql .= $set;

        $sql.= " where id_post = ".$post['id_post'];

        $result = $this->database->prepare($sql, $criteria);

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function delete(object $post): bool
    {
        $post = get_object_vars($post);

        $sql = "DELETE FROM post where id_post = " . $post['id_post'];

        $result = $this->database->query($sql);

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function nextPost(\DateTime $createdAt): int|null
    {

        $createdAt = $createdAt->format('Y-m-d H:i:s');

        $last = $this->database->query("SELECT * FROM post ORDER BY createdAt DESC LIMIT 0 ");
        $nextPost = $this->database->query("SELECT * 
        FROM post 
        WHERE createdAt < '$createdAt' 
        ORDER BY createdAt DESC LIMIT 0,1 ");

        if ($nextPost !== $last && is_iterable($nextPost)) {
                $nextPost = current($nextPost)->id_post;
                return (int)$nextPost;
        }

        return null;
    }

    public function previousPost(\DateTime $createdAt): int|null
    {

        $createdAt = $createdAt->format('Y-m-d H:i:s');


        $previousPost = $this->database->query("SELECT * FROM post WHERE createdAt > '$createdAt' LIMIT 0,1 ");

        if (!empty($previousPost) && is_iterable($previousPost)) {
                $previousPost = current($previousPost)->id_post;
                return (int)$previousPost;
        }

        return null;
    }

    public function countAllPosts():int
    {

        $data = $this->database->query("SELECT COUNT(*) AS nb FROM post ORDER BY createdAt desc ");

        if (is_array($data)) {
            $data = current($data);
        }

        return (int)$data->nb;
    }
}
