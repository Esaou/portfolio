<?php

declare(strict_types=1);

namespace  App\Service;

use App\Controller\Backoffice\CommentController;
use App\Controller\Backoffice\PostAdminController;
use App\Controller\Backoffice\UserAdminController;
use App\Controller\Frontoffice\ErrorController;
use App\Controller\Frontoffice\HomeController;
use App\Controller\Frontoffice\PostController;
use App\Controller\Frontoffice\SecurityController;
use App\Controller\Frontoffice\UserController;
use App\Model\Entity\User;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use App\Model\Repository\UserRepository;
use App\Service\Http\RedirectResponse;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\View\View;

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

        }elseif ($action === 'forbidden') {
            $controller = new SecurityController($this->view);
            return $controller->forbidden();

        }elseif ($action === 'postsAdmin') {
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new PostAdminController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);
            return $controller->postsList();
        }elseif ($action === 'comments') {
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new CommentController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);
            return $controller->commentList();
        }elseif ($action === 'users') {
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new UserAdminController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);
            return $controller->usersList();
        }elseif ($action === 'userAccount') {
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new UserAdminController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);
            return $controller->userAccount();
        } elseif ($action === 'editPost') {
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new PostAdminController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);
            return $controller->editPost((int) $this->request->query()->get('id'));
        }elseif ($action === 'addPost') {
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new PostAdminController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);
            return $controller->addPost();
        }elseif ($action === 'editUser') {
            $postRepo = new PostRepository($this->database);
            $userRepo = new UserRepository($this->database);
            $commentRepo = new CommentRepository($this->database);
            $controller = new UserAdminController($this->view,$this->request,$this->session,$commentRepo,$userRepo,$postRepo);
            return $controller->editUser((int) $this->request->query()->get('id'));
        }elseif ($action === 'userAccountFrontOffice') {
            $userRepo = new UserRepository($this->database);
            $controller = new UserController($userRepo, $this->view, $this->session,$this->request);
            return $controller->userAccount();
        }elseif ($action === 'postNotFound') {
            $controller = new SecurityController($this->view);
            return $controller->postNotFound();
        }elseif ($action === 'notFound') {
            $controller = new SecurityController($this->view);
            return $controller->notFound();
        }

        return new RedirectResponse('notFound');

    }
}
