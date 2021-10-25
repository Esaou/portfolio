<?php

declare(strict_types=1);

namespace  App\Service;

use App\Service\Http\RedirectResponse;
use App\Service\Http\Request;

class Router
{

    public $url;
    public $routes = [];

    private Request $request;
    private Environment $environment;
    private RedirectResponse $redirect;

    public function __construct($url,$request,$environment)
    {
        $this->url = trim($url, '/');

        $this->request = $request;
        $this->environment = $environment;
        $this->redirect = new RedirectResponse();

    }

    public function set(string $path, string $action,string $method)
    {
        $this->routes[$method][] = new Route($path, $action);
    }

    public function run()
    {
        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->matches($this->url)) {
                return $route->execute();
            }
        }

        $this->redirect->redirect('notFound');

    }
}
