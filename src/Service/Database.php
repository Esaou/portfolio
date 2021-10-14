<?php

declare(strict_types=1);

namespace App\Service;

use PDO;

class Database
{
    private string $dbName;
    private string $dbUser;
    private string $dbPass;
    private string $dbHost;
    private PDO $pdo;


    public function __construct(
        Environment $environment
    ) {

        $this->dbName = $environment->getDbName();
        $this->dbHost = $environment->getDbHost();
        $this->dbUser = $environment->getDbUser();
        $this->dbPass = $environment->getDbPass();

        $this->pdo = new PDO("mysql:dbname=$this->dbName;host=$this->dbHost", "$this->dbUser", "$this->dbPass");
    }

    public function query(string $statement): array|bool
    {
        $req = $this->pdo->query($statement);
        if (
            mb_strpos($statement, 'UPDATE') === 0 ||
            mb_strpos($statement, 'INSERT') === 0 ||
            mb_strpos($statement, 'DELETE') === 0
        ) {
            return true;
        }

        $datas = [];

        if ($req !== false) {
            $req->setFetchMode(PDO::FETCH_OBJ);
            $datas = $req->fetchAll();
        }


        return $datas;
    }

    public function prepare(string $statement, array $attributes): array|bool
    {
        $req = $this->pdo->prepare($statement);
        $res = $req->execute($attributes);

        if (
            mb_strpos($statement, 'UPDATE') === 0 ||
            mb_strpos($statement, 'INSERT') === 0 ||
            mb_strpos($statement, 'DELETE') === 0
        ) {
            return $res;
        }

        $req->setFetchMode(PDO::FETCH_OBJ);
        $datas = $req->fetchAll();

        return $datas;
    }

    public function setCondition(array $fields): string
    {
        $sqlParts = [];

        foreach (array_keys($fields) as $k) {
            $sqlParts[] = "$k = :$k";
        }

        $sqlParts = implode(' and ', $sqlParts);
        $sqlParts = 'where ' . $sqlParts;

        return $sqlParts;
    }

    public function setOrderBy(array $fields): string
    {
        $sqlParts = [];

        foreach ($fields as $k => $v) {
            $sqlParts[] = "$k $v";
        }

        $sqlParts = implode(' and ', $sqlParts);

        return $sqlParts;
    }

    public function setConditionUpdate(array $fields): string
    {
        $sqlParts = [];

        foreach ($fields as $k => $v) {
            if (is_string($v)) {
                $sqlParts[] = "$k = '$v'";
            }

            if (!is_string($v)) {
                $sqlParts[] = "$k = $v";
            }
        }

        $sqlParts = implode(' , ', $sqlParts);

        return $sqlParts;
    }

    public function setConditionUpdatePost(array $fields): string
    {
        $sqlParts = [];

        foreach (array_keys($fields) as $k) {
            $sqlParts[] = "$k = :$k";
        }

        $sqlParts = implode(' , ', $sqlParts);


        return $sqlParts;
    }
}
