<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use App\Service\Router;
use App\Service\Http\Request;

// TODO => créer un fichier .env pour mettre la configuration dedans.
const APP_ENV='dev';

if (APP_ENV === 'dev')
{
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}

$request = new Request($_GET, $_POST, $_FILES, $_SERVER);
$router = new Router($request);
$response = $router->run();
$response->send();
