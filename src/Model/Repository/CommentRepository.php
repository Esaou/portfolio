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
        $data = $this->database->query("select * from comment left join user on comment.user_id = user.id_utilisateur where id=$id");
        if (!empty($data)){
            $data = current($data);
        }

        if ($data === false) {
            return null;
        }

        $data->createdAt = new \DateTime($data->createdAt);


        $user = new User((int)$data->id_utilisateur, $data->firstname,$data->lastname, $data->email, $data->password,$data->role);


        return new Comment((int)$data->id, $data->content,(int)$data->post_id,$user,$data->isChecked,$data->createdAt);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Comment
    {
        $data = $this->findBy($criteria,$orderBy);
        if (!empty($data)){
            $data = current($data);
        }

        $user = new User((int)$data->user->id_utilisateur, $data->user->firstname,$data->user->lastname, $data->user->email, $data->user->password,$data->user->role);

        return $data === false ? null : new Comment((int)$data->id, $data->content,(int)$data->idPost,$user,$data->isChecked,$data->createdAt);
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


        $data = $this->database->prepare("select * from comment left join user on comment.user_id = user.id_utilisateur where $where order by $orderBy limit $limit offset $offset",$criteria);

        $data = json_decode(json_encode($data), true);

        if (empty($data)) {
            return null;
        }

        $comments = [];

        foreach ($data as $comment) {
            $comment['createdAt'] = new \DateTime($comment['createdAt']);
            $user = new User((int)$comment['id_utilisateur'], $comment['firstname'],$comment['lastname'], $comment['email'], $comment['password'],$comment['role']);
            $comments[] = new Comment((int)$comment['id'], $comment['content'],(int)$comment['post_id'],$user,$comment['isChecked'],$comment['createdAt']);
        }

        return $comments;
    }

    public function findAll(): ?array
    {
        $data = $this->database->query('select * from comment left join user on comment.user_id = user.id_utilisateur');

        if (empty($data)) {
            return null;
        }

        $comments = [];
        foreach ($data as $comment) {
            $comment->createdAt = new \DateTime($comment->createdAt);
            $user = new User((int)$comment->id_utilisateur, $comment->firstname,$comment->lastname, $comment->email, $comment->password,$comment->role);
            $comments[] = new Comment((int)$comment->id, $comment->content,(int)$comment->post_id,$user,$comment->isChecked,$comment->createdAt);
        }


        return $comments;
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
}
