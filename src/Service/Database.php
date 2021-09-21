<?php

declare(strict_types=1);

namespace App\Service;

use \PDO;

class Database
{
    private string $dbName;
    private string $dbUser;
    private string $dbPass;
    private string $dbHost;
    private PDO $pdo;


    public function __construct(string $dbName = 'projet5', string $dbUser = 'root', string $dbPass = '', string $dbHost = 'localhost')
    {
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;
        $this->dbHost = $dbHost;
    }

    public function getPDO(): object
    {
        if (!isset($this->pdo)) {
            $pdo = new PDO("mysql:dbname=$this->dbName;host=$this->dbHost", "$this->dbUser", "$this->dbPass");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo = $pdo;
        }
        return $this->pdo;
    }

    public function query(string $statement): array|bool
    {
        $req = $this->getPDO()->query($statement);
        if (mb_strpos($statement, 'UPDATE') === 0 ||
            mb_strpos($statement, 'INSERT') === 0 ||
            mb_strpos($statement, 'DELETE') === 0) {
            return true;
        }

        $req->setFetchMode(PDO::FETCH_OBJ);

        $datas = $req->fetchAll();


        return $datas;
    }

    public function prepare(string $statement, array $attributes): array|bool
    {
        $req = $this->getPDO()->prepare($statement);
        $res = $req->execute($attributes);

        if (mb_strpos($statement, 'UPDATE') === 0 ||
            mb_strpos($statement, 'INSERT') === 0 ||
            mb_strpos($statement, 'DELETE') === 0) {
            return $res;
        }

        $req->setFetchMode(PDO::FETCH_OBJ);
        $datas = $req->fetchAll();

        return $datas;
    }

    public function setCondition(array $fields):string
    {
        $sqlParts = [];

        foreach ($fields as $k => $v) {
            $sqlParts[] = "$k = :$k";
        }

        $sqlParts = implode(' and ', $sqlParts);
        $sqlParts = 'where ' . $sqlParts;

        return $sqlParts;
    }

    public function setOrderBy(array $fields):string
    {
        $sqlParts = [];

        foreach ($fields as $k => $v) {
            $sqlParts[] = "$k $v";
        }

        $sqlParts = implode(' and ', $sqlParts);

        return $sqlParts;
    }

    public function setConditionUpdate(array $fields):string
    {
        $sqlParts = [];

        foreach ($fields as $k => $v) {
            if (is_string($v)) {
                $sqlParts[] = "$k = '$v'";
            } else {
                $sqlParts[] = "$k = $v";
            }
        }

        $sqlParts = implode(' , ', $sqlParts);

        return $sqlParts;
    }

    public function setConditionUpdatePost(array $fields):string
    {
        $sqlParts = [];

        foreach ($fields as $k => $v) {
            $sqlParts[] = "$k = :$k";
        }

        $sqlParts = implode(' , ', $sqlParts);


        return $sqlParts;
    }
}
