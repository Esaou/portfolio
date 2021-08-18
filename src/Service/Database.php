<?php

declare(strict_types=1);

// class pour gérer la connection à la base de donnée
namespace App\Service;

// *** exemple fictif d'accès à la base de données
final class Database
{
    private array $bdd;
    private string $table;

    public function __construct()
    {
        /* A retirer - Début - Ne pas analyser ce code*/
        // table user
        $this->bdd['user']['jean@free.fr'] = ['id' => 1, 'email' => 'jean@free.fr', 'pseudo' => 'jean', 'password' => 'password'];
        // table post
        $this->bdd['post'][1] = ['id' => 1, 'title' => 'Article $1 du blog', 'text' => 'Lorem ipsum 1'];
        $this->bdd['post'][25] = ['id' => 25, 'title' => 'Article $25 du blog', 'text' => 'Lorem ipsum 25'];
        $this->bdd['post'][26] = ['id' => 26, 'title' => 'Article $26 du blog', 'text' => 'Lorem ipsum 26'];
        // table comment
        $this->bdd['comment'][1] = [
            ['id' => 1, 'pseudo' => 'Maurice', 'text' => 'J\'aime bien', 'idPost' => '1'],
            ['id' => 4, 'pseudo' => 'Eric', 'text' => 'bof !!!', 'idPost' => '1'],
        ];
        $this->bdd['comment'][25] = [
            ['id' => 2, 'pseudo' => 'Marc', 'text' => 'Cool', 'idPost' => '25'],
            ['id' => 3, 'pseudo' => 'Jean', 'text' => 'Je n\'ai pas compris', 'idPost' => '25'],
        ];
        $this->bdd['comment'][26] = null;
        /* A retirer - Fin */
    }

    /* A retirer - Début - Ne pas analyser ce code */
    public function prepare(string $sql): void
    {
        $table = explode('from', $sql);
        $table = explode('where', $table[1]);
        $this->table = trim($table[0]);
    }

    public function execute(array $criteria = null): ?array
    {
        if ($criteria !== null) {
            $criteria = array_shift($criteria);

            if (!array_key_exists($criteria, $this->bdd[$this->table])) {
                return null;
            }
            return $this->bdd[$this->table][$criteria];
        }

        return $this->bdd[$this->table];
    }


    /* A retirer - Fin */
}
