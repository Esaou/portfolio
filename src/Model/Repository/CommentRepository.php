<?php

declare(strict_types=1);

namespace App\Model\Repository;

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

    public function find(int $id): ?Comment
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

    public function findOneBy(array $criteria, array $orderBy = null): ?Comment
    {
        $data = $this->findBy($criteria,$orderBy);

        if (!is_null($data)){
            $data = current($data);
        }

        return $data === null ? null : $data;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $sql = "select * from comment left join user on comment.user_id = user.id_utilisateur ";

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

        $comments = [];

        foreach ($data as $comment) {
            $comment->createdAt = new \DateTime($comment->createdAt);
            $user = new User((int)$comment->id_utilisateur, $comment->firstname,$comment->lastname, $comment->email, $comment->password,$comment->isValid,$comment->role,$comment->token);
            $comments[] = new Comment((int)$comment->id, $comment->content,(int)$comment->post_id,$user,$comment->isChecked,$comment->createdAt);
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

        $criteria = false;

        foreach ($comment as $key => $value) {

            if ($key === 'createdAt'){
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            }elseif ($key === 'user'){
                $criteria[$key] = $value->id_utilisateur;
            } elseif ($key === 'id'){

            }else{
                $criteria[$key] = $value;
            }
        }

        $sql = "INSERT INTO comment (user_id,content, post_id,isChecked,createdAt) VALUES (:user,:content,:post_id,:isChecked,:createdAt )";
        $result = $this->database->prepare($sql,$criteria);

        if ($result === true){
            return true;
        }else{
            return false;
        }

    }

    public function update(object $comment): bool
    {
        return false;
    }

    public function delete(object $comment): bool
    {
        return false;
    }

    public function countAllCheckedComment(int $id):int
    {
        $data = $this->database->query("SELECT COUNT(*) AS nb FROM comment WHERE post_id = $id and isChecked = 'Oui' ORDER BY id DESC");
        $data = current($data);
        return (int)$data->nb;
    }
}
