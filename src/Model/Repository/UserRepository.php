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

    public function find(int $idUser): ?User
    {
        $data = $this->findBy(['id'=>$idUser]);

        if (!empty($data)) {
            $data = current($data);
        }

        if ($data === false) {
            return null;
        }

        return $data;
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?User
    {
        $data = $this->findBy($criteria, $orderBy);

        if ($data !== null) {
            $data = current($data);
        }

        return $data === null ? null : $data;
    }

    public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): ?array
    {
        $sql = "select * from user ";

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

        $users = [];

        if (is_iterable($data)) {
            foreach ($data as $user) {
                $users[] = new User(
                    (int)$user->id_utilisateur,
                    $user->firstname,
                    $user->lastname,
                    $user->email,
                    $user->password,
                    $user->isValid,
                    $user->role,
                    $user->token
                );
            }
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

        $user = get_object_vars($user);

        foreach ($user as $key => $value) {
            $criteria[$key] = $value;
        }

        $sql = "INSERT INTO user (id_utilisateur,firstname, lastname,email,password,isValid,role,token) 
                VALUES (:id_utilisateur,:firstname,:lastname,:email,:password,:isValid,:role,:token )";

        $result = '';

        if (is_array($criteria)) {
            $result = $this->database->prepare($sql, $criteria);
        }

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function update(object $user): bool
    {
        $criteria = [];

        $user = get_object_vars($user);

        foreach ($user as $key => $value) {
            $criteria[$key] = $value;
        }

        $criteria = $this->database->setConditionUpdate($criteria);

        $sql = "UPDATE user SET ";

        $sql .= $criteria;

        $sql.= " where id_utilisateur = ".$user['id_utilisateur'];


        $result = $this->database->query($sql);

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function delete(object $user): bool
    {
        $user = get_object_vars($user);

        $sql = "DELETE FROM user where id_utilisateur = " . $user['id_utilisateur'];

        $result = $this->database->query($sql);

        if ($result === true) {
            return true;
        }

        return false;
    }

    public function countAllUsers(): int
    {
        $data = $this->database->query("SELECT COUNT(*) AS nb FROM user ORDER BY id_utilisateur DESC");

        if (is_array($data)) {
            $data = current($data);
        }

        return (int)$data->nb;
    }
}
