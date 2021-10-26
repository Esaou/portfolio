<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Http\RedirectResponse;

class Router
{

    public string $url;
    public array $routes = [];
    private RedirectResponse $redirect;

    public function __construct(string $url)
    {
        $this->url = trim($url, '/');
        $this->redirect = new RedirectResponse();
        $this->load();
    }

    public function load():void
    {

        $lignes = false;

        if (file_exists(__DIR__ . '/../../config/routes/routes.txt')) {
            $lignes = file(__DIR__ . '/../../config/routes/routes.txt');
        }

        $array = [];
        $key = 0;

        if (is_iterable($lignes)) {
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
                if (is_string($item['route']) && is_string($item['action'])) {
                    $this->set($item['route'], $item['action']);
                }
            }
        }
    }

    public function set(string $path, string $action):void
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
