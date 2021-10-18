<?php

declare(strict_types=1);

namespace  App\Service;

use App\Service\Http\Request;

class Router
{

    public $url;
    public $routes = [];

    private Request $request;
    private Environment $environment;

    public function __construct($url,$request,$environment)
    {
        $this->url = trim($url, '/');

        $this->request = $request;
        $this->environment = $environment;

    }

    public function set(string $path, string $action,string $method)
    {
        $this->routes[$method][] = new Route($path, $action,$this->request,$this->environment);
    }

    public function run()
    {
        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if ($route->matches($this->url)) {
                return $route->execute();
            }
        }

    }
}

/*use App\Controller\Backoffice\CommentController;
use App\Controller\Backoffice\PostAdminController;
use App\Controller\Backoffice\UserAdminController;
use App\Controller\Frontoffice\ErrorController;
use App\Controller\Frontoffice\HomeController;
use App\Controller\Frontoffice\PostController;
use App\Controller\Frontoffice\UserController;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use App\Model\Repository\UserRepository;
use App\Service\FormValidator\AccountValidator;
use App\Service\FormValidator\CommentValidator;
use App\Service\FormValidator\ContactValidator;
use App\Service\FormValidator\EditPostValidator;
use App\Service\FormValidator\LoginValidator;
use App\Service\FormValidator\RegisterValidator;
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
    private RedirectResponse $redirect;
    private Mailer $mailer;
    private CsrfToken $csrf;
    private Paginator $paginator;
    private Authorization $security;
    private UserRepository $userRepo;
    private LoginValidator $loginValidator;
    private RegisterValidator $registerValidator;
    private AccountValidator $accountValidator;
    private PostRepository $postRepo;
    private CommentRepository $commentRepo;
    private Environment $environment;

    public function __construct(Request $request, Environment $environment)
    {
        // dépendance
        $this->request = $request;
        $this->environment = $environment;
        $this->database = new Database($this->environment);
        $this->session = new Session();
        $this->view = new View($this->session);
        $this->paginator = new Paginator($this->request, $this->view);
        $this->redirect = new RedirectResponse();
        $this->mailer = new Mailer($this->view, $this->session);
        $this->csrf = new CsrfToken($this->session, $this->request);
        $this->security = new Authorization($this->session, $this->request);
        $this->userRepo = new UserRepository($this->database);
        $this->postRepo = new PostRepository($this->database);
        $this->commentRepo = new CommentRepository($this->database);
        $this->loginValidator = new LoginValidator($this->session);
        $this->registerValidator = new RegisterValidator($this->session);
        $this->accountValidator = new AccountValidator($this->session);
    }

    public function run(): Response
    {
        $action = $this->request->query()->has('action') ? $this->request->query()->get('action') : 'home';

        if ($action === 'posts') {
            //injection des dépendances et instanciation du controller
            $validator = new CommentValidator($this->session);
            $controller = new PostController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $validator,
                $this->csrf,
                $this->paginator,
                $this->redirect
            );

            return $controller->displayAllAction();
        } elseif ($action === 'post' && $this->request->query()->has('id')) {
            //injection des dépendances et instanciation du controller
            $validator = new CommentValidator($this->session);
            $controller = new PostController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $validator,
                $this->csrf,
                $this->paginator,
                $this->redirect
            );

            return $controller->displayOneAction((int) $this->request->query()->get('id'));
        } elseif ($action === 'login') {
            $controller = new UserController(
                $this->userRepo,
                $this->view,
                $this->session,
                $this->request,
                $this->loginValidator,
                $this->registerValidator,
                $this->accountValidator,
                $this->mailer,
                $this->security,
                $this->csrf,
                $this->redirect
            );

            return $controller->loginAction($this->request);
        } elseif ($action === 'logout') {
            $controller = new UserController(
                $this->userRepo,
                $this->view,
                $this->session,
                $this->request,
                $this->loginValidator,
                $this->registerValidator,
                $this->accountValidator,
                $this->mailer,
                $this->security,
                $this->csrf,
                $this->redirect
            );

            return $controller->logoutAction();
        } elseif ($action === 'home') {
            $validator = new ContactValidator($this->session);
            $controller = new HomeController(
                $this->view,
                $this->request,
                $this->session,
                $validator,
                $this->csrf,
                $this->mailer
            );
            return $controller->home();
        } elseif ($action === 'register') {
            $controller = new UserController(
                $this->userRepo,
                $this->view,
                $this->session,
                $this->request,
                $this->loginValidator,
                $this->registerValidator,
                $this->accountValidator,
                $this->mailer,
                $this->security,
                $this->csrf,
                $this->redirect
            );
            return $controller->register();
        } elseif ($action === 'confirmUser') {
            $controller = new UserController(
                $this->userRepo,
                $this->view,
                $this->session,
                $this->request,
                $this->loginValidator,
                $this->registerValidator,
                $this->accountValidator,
                $this->mailer,
                $this->security,
                $this->csrf,
                $this->redirect
            );
            return $controller->confirmUser();
        } elseif ($action === 'forbidden') {
            $controller = new ErrorController($this->view);
            return $controller->forbidden();
        } elseif ($action === 'postsAdmin') {
            $editPostValidator = new EditPostValidator($this->session);
            $controller = new PostAdminController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $editPostValidator,
                $this->csrf,
                $this->paginator,
                $this->security,
                $this->redirect,
            );
            return $controller->postsList();
        } elseif ($action === 'deletePost') {
            $editPostValidator = new EditPostValidator($this->session);
            $controller = new PostAdminController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $editPostValidator,
                $this->csrf,
                $this->paginator,
                $this->security,
                $this->redirect,
            );
            return $controller->deletePost((int) $this->request->request()->get('id'));
        } elseif ($action === 'comments') {
            $controller = new CommentController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $this->paginator,
                $this->security,
                $this->redirect
            );
            return $controller->commentList();
        } elseif ($action === 'deleteComment') {
            $controller = new CommentController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $this->paginator,
                $this->security,
                $this->redirect
            );
            return $controller->deleteComment((int) $this->request->request()->get('id'));
        } elseif ($action === 'validateComment') {
            $controller = new CommentController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $this->paginator,
                $this->security,
                $this->redirect
            );
            return $controller->validateComment((int) $this->request->request()->get('id'));
        } elseif ($action === 'unvalidateComment') {
            $controller = new CommentController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $this->paginator,
                $this->security,
                $this->redirect
            );
            return $controller->unvalidateComment((int) $this->request->request()->get('id'));
        } elseif ($action === 'users') {
            $editPostValidator = new EditPostValidator($this->session);
            $controller = new UserAdminController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $this->security,
                $editPostValidator,
                $this->accountValidator,
                $this->csrf,
                $this->paginator,
                $this->redirect
            );
            return $controller->usersList();
        } elseif ($action === 'deleteUser') {
            $editPostValidator = new EditPostValidator($this->session);
            $controller = new UserAdminController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $this->security,
                $editPostValidator,
                $this->accountValidator,
                $this->csrf,
                $this->paginator,
                $this->redirect
            );
            return $controller->deleteUser((int) $this->request->request()->get('id'));
        } elseif ($action === 'userAccount') {
            $editPostValidator = new EditPostValidator($this->session);
            $controller = new UserAdminController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $this->security,
                $editPostValidator,
                $this->accountValidator,
                $this->csrf,
                $this->paginator,
                $this->redirect
            );
            return $controller->userAccount((int) $this->request->request()->get('id'));
        } elseif ($action === 'editPost') {
            $editPostValidator = new EditPostValidator($this->session);
            $controller = new PostAdminController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $editPostValidator,
                $this->csrf,
                $this->paginator,
                $this->security,
                $this->redirect,
            );
            return $controller->editPost((int) $this->request->query()->get('id'));
        } elseif ($action === 'addPost') {
            $editPostValidator = new EditPostValidator($this->session);
            $controller = new PostAdminController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $editPostValidator,
                $this->csrf,
                $this->paginator,
                $this->security,
                $this->redirect,
            );
            return $controller->addPost();
        } elseif ($action === 'editUser') {
            $editPostValidator = new EditPostValidator($this->session);
            $controller = new UserAdminController(
                $this->view,
                $this->request,
                $this->session,
                $this->commentRepo,
                $this->userRepo,
                $this->postRepo,
                $this->security,
                $editPostValidator,
                $this->accountValidator,
                $this->csrf,
                $this->paginator,
                $this->redirect
            );
            return $controller->editUser((int) $this->request->query()->get('id'));
        } elseif ($action === 'userAccountFrontOffice') {
            $controller = new UserController(
                $this->userRepo,
                $this->view,
                $this->session,
                $this->request,
                $this->loginValidator,
                $this->registerValidator,
                $this->accountValidator,
                $this->mailer,
                $this->security,
                $this->csrf,
                $this->redirect
            );
            return $controller->userAccount((int) $this->request->request()->get('id_utilisateur'));
        } elseif ($action === 'postNotFound') {
            $controller = new ErrorController($this->view);
            return $controller->postNotFound();
        } elseif ($action === 'notFound') {
            $controller = new ErrorController($this->view);
            return $controller->notFound();
        }

        return new Response(
            $this->view->render(
                [
                'type' => 'frontoffice',
                'template' => 'notFound'
                ]
            ), 404
        );
    }
}*/
