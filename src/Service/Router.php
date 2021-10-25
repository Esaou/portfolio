<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Http\RedirectResponse;

class Router
{

    public $url;
    public $routes = [];

    private RedirectResponse $redirect;

    public function __construct($url)
    {
        $this->url = trim($url, '/');
        $this->redirect = new RedirectResponse();

        if (file_exists(__DIR__ . '/../../config/routes/routes.txt')) {
            $lignes = file(__DIR__ . '/../../config/routes/routes.txt');
            $this->load($lignes);
        }
    }

    public function load($lignes)
    {
        $array = [];
        $key = 0;
        foreach ($lignes as $ligne) {
            $delimiteur_route = strpos($ligne, "\r\n");

            if ($delimiteur_route === 0) {
                $array[$key++];
            }

            $delemiteur_position = strpos($ligne, '=');

            if ($delemiteur_position !== false) {
                $clef = trim(substr($ligne, 0, $delemiteur_position));
                $valeur = trim(substr($ligne, $delemiteur_position + 2));

                if (!array_key_exists($clef, $array)) {
                    if ($clef === 'methods') {
                        $array[$key][$clef] = explode(",", $valeur);
                    } else {
                        $array[$key][$clef] = $valeur;
                    }
                }
            }
        }

        foreach ($array as $item) {
            $this->set($item['route'], $item['action']);
        }
    }

    public function set(string $path, string $action)
    {
        $this->routes[] = new Route($path, $action);
    }

    public function run()
    {
        foreach ($this->routes as $route) {
            if ($route->matches($this->url)) {
                return $route->execute();
            }
        }

        $this->redirect->redirect('notFound');
    }
}
