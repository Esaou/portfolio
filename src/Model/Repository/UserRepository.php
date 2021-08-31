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
        $data = $this->database->query("select * from user where id_utilisateur=$id");

        if (!empty($data)){
            $data = current($data);
        }

        if ($data === false) {
            return null;
        }

        return new User((int)$data['id_utilisateur'], $data['firstname'],$data['lastname'], $data['email'], $data['password'],$data['role']);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?User
    {
        $data = $this->findBy($criteria,$orderBy);

        if (!empty($data)){
            $data = current($data);
        }

        return $data === false ? null : new User((int)$data->id_utilisateur, $data->firstname,$data->lastname, $data->email, $data->password,$data->role);
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $where = $this->database->setCondition($criteria);

        if ($orderBy == null){
            $orderBy = "id_utilisateur desc";
        }else{
            $orderBy = $this->database->setOrderBy($orderBy);
        }

        if ($limit == null){
            $limit = 1000000;
        }
        if ($offset == null){
            $offset = 0;
        }

        $data = $this->database->prepare("select * from user where $where order by $orderBy limit $limit offset $offset",$criteria);


        $data = json_decode(json_encode($data), true);

        if (empty($data)) {
            return null;
        }

        $users = [];

        foreach ($data as $user) {
            $users[] = new User((int)$user['id_utilisateur'], $user['firstname'],$user['lastname'], $user['email'], $user['password'],$user['role']);
        }

        return $users;
    }

    public function findAll(): ?array
    {
        $data = $this->database->query('select * from user');

        if (empty($data)) {
            return null;
        }

        $users = [];
        foreach ($data as $user) {
            $users[] = new User((int)$user->id_utilisateur, $user->firstname, $user->lastname,$user->email,$user->password,$user->role);
        }

        return $users;
    }

    public function create(object $user): bool
    {
        return false;
    }

    public function update(object $user): bool
    {
        return false;
    }

    public function delete(object $user): bool
    {
        return false;
    }
}
