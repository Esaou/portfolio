<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Model\Entity\Post;
use App\Model\Entity\User;
use App\Service\Database;
use App\Model\Entity\Comment;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;
use function Couchbase\defaultDecoder;

final class CommentRepository implements EntityRepositoryInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function find(int $idComment): ?Comment
    {
        $data = $this->findBy(['id'=>$idComment]);

        if (!empty($data)) {
            $data = current($data);
        }

        if ($data === false) {
            return null;
        }

        return $data;
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Comment
    {
        $data = $this->findBy($criteria, $orderBy);

        if (!is_null($data)) {
            $data = current($data);
        }

        return $data === null ? null : $data;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $sql = "select *
                from comment
                left join user on comment.id_user = user.id_utilisateur
                left join post on comment.post_id = post.id_post ";

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

        $comments = [];

        if (is_iterable($data)) {
            foreach ($data as $comment) {
                $comment->createdAt = new \DateTime($comment->createdAt);
                if (!is_null($comment->updatedAt)) {
                    $comment->updatedAt = new \DateTime($comment->updatedAt);
                }
                $comment->createdDate = new \DateTime($comment->createdDate);
                $post = new Post(
                    (int)$comment->id_post,
                    $comment->chapo,
                    $comment->title,
                    $comment->content,
                    $comment->createdAt,
                    $comment->updatedAt,
                    null
                );
                $user = new User(
                    (int)$comment->id_utilisateur,
                    $comment->firstname,
                    $comment->lastname,
                    $comment->email,
                    $comment->password,
                    $comment->isValid,
                    $comment->role,
                    $comment->token
                );
                $comments[] = new Comment(
                    (int)$comment->id,
                    $comment->comment,
                    $post,
                    $user,
                    $comment->isChecked,
                    $comment->createdDate
                );
            }
        }

        return $comments;
    }

    public function findAll(): ?array
    {
        $data = $this->findBy([]);

        if (empty($data)) {
            return null;
        }

        return $data;
    }

    public function create(object $comment): bool
    {

        $criteria = [];

        $comment = get_object_vars($comment);

        foreach ($comment as $key => $value) {
            if ($key !== 'createdDate' && $key !== 'id_user' && $key !== 'post_id' && $key !== 'id') {
                $criteria[$key] = $value;
            } elseif ($key === 'createdDate') {
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            } elseif ($key === 'id_user') {
                $criteria[$key] = $value->id_utilisateur;
            } elseif ($key === 'post_id') {
                $criteria[$key] = $value->id_post;
            }
        }

        $sql = "INSERT INTO comment (id_user,comment, post_id,isChecked,createdDate)
                VALUES (:id_user,:comment,:post_id,:isChecked,:createdDate )";

        $result = $this->database->prepare($sql, $criteria);

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function update(object $comment): bool
    {
        $criteria = [];

        $comment = get_object_vars($comment);

        foreach ($comment as $key => $value) {
            if ($key === 'createdDate') {
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            } elseif ($key === 'id_user') {
                $criteria['id_user'] = $value->id_utilisateur;
            } elseif ($key === 'post_id') {
                $criteria['post_id'] = $value->id_post;
            } else {
                $criteria[$key] = $value;
            }
        }

        $set = $this->database->setConditionUpdatePost($criteria);

        $sql = "UPDATE comment SET ";

        $sql .= $set;

        $sql.= " where id = ".$comment['id'];

        $result = $this->database->prepare($sql, $criteria);

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function delete(object $comment): bool
    {
        $comment = get_object_vars($comment);

        $sql = "DELETE FROM comment where id = " . $comment['id'];

        $result = $this->database->query($sql);

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function countAllCheckedComment(int $idComment):int
    {
        $data = $this->database->query("SELECT COUNT(*) AS nb 
            FROM comment 
            WHERE post_id = $idComment and isChecked = 'Oui' 
            ORDER BY id DESC");

        if (is_iterable($data)) {
            $data = current($data);
        }

        return (int)$data->nb;
    }

    public function countAllComment():int
    {
        $data = $this->database->query("SELECT COUNT(*) AS nb FROM comment ORDER BY id DESC");

        if (is_iterable($data)) {
            $data = current($data);
        }

        return (int)$data->nb;
    }
}
