<?php

declare(strict_types=1);

namespace App\Model\Repository;

use App\Service\Database;
use App\Model\Entity\User;
use App\Model\Repository\Interfaces\EntityRepositoryInterface;

final class UserRepository implements EntityRepositoryInterface
{
    private Database $database;


    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function find(int $id): ?User
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

    public function findOneBy(array $criteria, array $orderBy = null): ?User
    {
        $data = $this->findBy($criteria,$orderBy);

        if (!is_null($data)){
            $data = current($data);
        }

        return $data === null ? null : $data;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $sql = "select * from user ";

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

        $users = [];

        foreach ($data as $user) {
            $users[] = new User((int)$user->id_utilisateur, $user->firstname,$user->lastname, $user->email, $user->password,$user->isValid,$user->role,$user->token);
        }

        return $users;
    }

    public function findAll(): ?array
    {
        $data = $this->findBy([]);

        if (empty($data)) {
            return null;
        }

        return $data;
    }

    public function create(object $user): bool
    {
        $criteria = false;

        foreach ($user as $key => $value) {

            $criteria[$key] = $value;

        }

        $sql = "INSERT INTO user (id_utilisateur,firstname, lastname,email,password,isValid,role,token) VALUES (:id_utilisateur,:firstname,:lastname,:email,:password,:isValid,:role,:token )";
        $result = $this->database->prepare($sql,$criteria);

        if ($result === true){
            return true;
        }else{
            return false;
        }
    }

    public function update(object $user): bool
    {
        $criteria = [];

        foreach ($user as $key => $value) {

            $criteria[$key] = $value;

        }

        $criteria = $this->database->setConditionUpdate($criteria);

        $sql = "UPDATE user SET ";

        $sql .= $criteria;

        $sql.= " where id_utilisateur = ".$user->id_utilisateur;


        $result = $this->database->query($sql);

        if ($result === true){
            return true;
        }else{
            return false;
        }
    }

    public function delete(object $user): bool
    {
        return false;
    }

}
