<?php

declare(strict_types=1);

namespace  App\Service;

use App\Controller\Backoffice\PostAdminController;
use App\Controller\Frontoffice\ErrorController;
use App\Controller\Frontoffice\HomeController;
use App\Controller\Frontoffice\PostController;
use App\Controller\Frontoffice\SecurityController;
use App\Controller\Frontoffice\UserController;
use App\Model\Entity\User;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use App\Model\Repository\UserRepository;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\View\View;

// TODO cette classe router est un exemple très basic. Cette façon de faire n'est pas optimale
// TODO Le router ne devrait pas avoir la responsabilité de l'injection des dépendances
final class Router
{
    private Database $database;
    private View $view;
    private Request $request;
    private Session $session;

    public function __construct(Request $request)
    {
        // dépendance
        $this->database = new Database();
        $this->session = new Session();
        $this->view = new View($this->session);
        $this->request = $request;
    }

    public function run(): Response
    {

        $action = $this->request->query()->has('action') ? $this->request->query()->get('action') : 'home';

        if ($action === 'posts') {
            //injection des dépendances et instanciation du controller
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new PostController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);

            return $controller->displayAllAction();

        } elseif ($action === 'post' && $this->request->query()->has('id')) {
            //injection des dépendances et instanciation du controller
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new PostController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);

            return $controller->displayOneAction((int) $this->request->query()->get('id'));

        } elseif ($action === 'login') {
            $userRepo = new UserRepository($this->database);
            $controller = new UserController($userRepo, $this->view, $this->session,$this->request);

            return $controller->loginAction($this->request);

        } elseif ($action === 'logout') {
            $userRepo = new UserRepository($this->database);
            $controller = new UserController($userRepo, $this->view, $this->session,$this->request);

            return $controller->logoutAction();
        } elseif ($action === 'home') {
            $controller = new HomeController($this->view,$this->request,$this->session);
            return $controller->home();

        } elseif ($action === 'register') {
            $userRepo = new UserRepository($this->database);
            $controller = new UserController($userRepo, $this->view, $this->session,$this->request);
            return $controller->register();

        }elseif ($action === 'confirmUser') {
            $userRepo = new UserRepository($this->database);
            $controller = new UserController($userRepo, $this->view, $this->session,$this->request);
            return $controller->confirmUser();

        }elseif ($action === 'postsAdmin') {
        //injection des dépendances et instanciation du controller
        $postRepo = new PostRepository($this->database);
        $userRepo = new UserRepository($this->database);
        $commentRepo = new CommentRepository($this->database);
        $controller = new PostAdminController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);

        return $controller->postsList();

    }

        return new Response($this->view->render(
            [
                'template' => 'notFound',
            ],
        ));

    }
}
