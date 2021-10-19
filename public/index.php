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


$request = new Request();

$router = new Router($request->server()->get('REQUEST_URI'), $request, $environment);

$router->set(
    '/',
    'App\Controller\Frontoffice\HomeController@home',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/posts',
    'App\Controller\Frontoffice\PostController@displayAllAction',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/posts/page/:page',
    'App\Controller\Frontoffice\PostController@displayAllAction',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/post/:id',
    'App\Controller\Frontoffice\PostController@displayOneAction',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/post/:id/page/:page',
    'App\Controller\Frontoffice\PostController@displayOneAction',
    $request->server()->get('REQUEST_METHOD')
);


$router->set(
    '/register',
    'App\Controller\Frontoffice\UserController@register',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/login',
    'App\Controller\Frontoffice\UserController@loginAction',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/register',
    'App\Controller\Frontoffice\UserController@register',
    $request->server()->get('REQUEST_METHOD')
);

// ADMIN

$router->set(
    '/admin/posts',
    'App\Controller\Backoffice\PostAdminController@postsList',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/admin/posts/page/:page',
    'App\Controller\Backoffice\PostAdminController@postsList',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/admin/add/post',
    'App\Controller\Backoffice\PostAdminController@addPost',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/admin/edit/post/:id',
    'App\Controller\Backoffice\PostAdminController@editPost',
    $request->server()->get('REQUEST_METHOD')
);

$router->set(
    '/admin/comments',
    'App\Controller\Backoffice\CommentController@commentList',
    $request->server()->get('REQUEST_METHOD')
);

// Route pour PAGINATION COMMENTAIRE -> erreur : reprendre ici

$router->set(
    '/admin/comments/page/:page',
    'App\Controller\Backoffice\CommentController@commentList',
    $request->server()->get('REQUEST_METHOD')
);

$response = $router->run();
$response->send();
