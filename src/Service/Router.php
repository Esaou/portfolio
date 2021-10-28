<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Http\RedirectResponse;

class Router
{

    public array $routes = [];
    private RedirectResponse $redirect;

    public function __construct()
    {
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
                    $this->set($item['route'], $item['action'],$item['methods']);
                }
            }
        }
    }

    public function set(string $path, string $action,array $methods):void
    {

        if (empty($this->routes['GET'])) {
            $this->routes['GET']=[];
        }

        if (empty($this->routes['POST'])) {
            $this->routes['POST']=[];
        }

        $route = new Route($path, $action);

        foreach ($methods as $method) {
            array_push($this->routes[$method],$route);
        }
    }

    public function run(string $url,string $method)
    {

        $url = trim($url, '/');
        foreach ($this->routes[$method] as $route) {
            if ($route->matches($url)) {
                return $route->execute();
            }
        }

        $this->redirect->redirect('notFound');
    }
}
