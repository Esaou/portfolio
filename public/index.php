<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use App\Service\Environment;
use App\Service\Router;
use App\Service\Http\Request;

$environment = new Environment();

if ($environment->getAppEnv() === 'dev') {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}

$request = new Request($_GET, $_POST, $_FILES, $_SERVER);

$router = new Router($request->server()->get('REQUEST_URI'), $request, $environment);

$router->set(
    '/posts',
    'App\Controller\Frontoffice\PostController@displayAllAction',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/',
    'App\Controller\Frontoffice\HomeController@home',
    $request->server()->get('REQUEST_METHOD')
);

$response = $router->run();
$response->send();
