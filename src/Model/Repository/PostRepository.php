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
            $posts[] = new Post((int)$post->id_post,$post->chapo, $post->title, $post->content,$post->createdAt,$post->updatedAt,$user);
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
        $criteria = false;

        foreach ($post as $key => $value) {

            if ($key === 'createdAt'){
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            }elseif ($key === 'user'){
                $criteria[$key] = $value->id_utilisateur;
            } elseif ($key === 'id_post'){

            }else{
                $criteria[$key] = $value;
            }
        }

        $sql = "INSERT INTO post (title,chapo,content, createdAt,updatedAt,user_id) VALUES (:title,:chapo,:content,:createdAt,:updatedAt,:user )";
        $result = $this->database->prepare($sql,$criteria);

        if ($result === true){
            return true;
        }else{
            return false;
        }
    }

    public function update(object $post): bool
    {
        $criteria = [];

        foreach ($post as $key => $value) {

            if ($key === 'createdAt'){
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            }elseif ($key === 'updatedAt'){
                $criteria[$key] = $value->format('Y-m-d H:i:s');
            }
            elseif ($key === 'user'){
                $criteria['user_id'] = $value->id_utilisateur;
            }else{
                $criteria[$key] = $value;
            }

        }

        $set = $this->database->setConditionUpdatePost($criteria);

        $sql = "UPDATE post SET ";

        $sql .= $set;

        $sql.= " where id_post = ".$post->id_post;

        $result = $this->database->prepare($sql,$criteria);

        if ($result === true){
            return true;
        }else{
            return false;
        }
    }

    public function delete(object $post): bool
    {
        $sql = "DELETE FROM post where id_post = $post->id_post ";

        $result = $this->database->query($sql);

        if ($result === true){
            return true;
        }else{
            return false;
        }
    }

    public function previousPost(\DateTime $createdAt): int|null
    {

        $createdAt = $createdAt->format('Y-m-d H:i:s');

        $last = $this->database->query("SELECT * FROM post ORDER BY createdAt DESC LIMIT 0 ");
        $nextPost = $this->database->query("SELECT * FROM post WHERE createdAt < '$createdAt' LIMIT 0,1 ");

        if($nextPost !== $last){

            $nextPost = current($nextPost)->id_post;
            return (int)$nextPost;

        }else{

            return null;

        }
    }

    public function nextPost(\DateTime $createdAt): int|null
    {

        $date = $createdAt->format('Y-m-d H:i:s');

        $previousPost = $this->database->query("SELECT * FROM post WHERE createdAt > '$date' ORDER BY createdAt DESC LIMIT 0,1 ");

        if(!empty($previousPost)){

            $previousPost = current($previousPost)->id_post;
            return (int)$previousPost;

        }else{

            return null;

        }
    }

    public function countAllPosts():int
    {

        $data = $this->database->query("SELECT COUNT(*) AS nb FROM post ORDER BY createdAt desc ");
        $data = current($data);

        return (int)$data->nb;
    }
}
