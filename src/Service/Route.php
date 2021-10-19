<?php


namespace App\Service;

use App\Model\Repository\CommentRepository;
use App\Model\Repository\PostRepository;
use App\Model\Repository\UserRepository;
use App\Service\FormValidator\AccountValidator;
use App\Service\FormValidator\ContactValidator;
use App\Service\FormValidator\LoginValidator;
use App\Service\FormValidator\RegisterValidator;
use App\Service\Http\RedirectResponse;
use App\Service\Http\Request;
use App\Service\Http\Session\Session;
use App\View\View;

class Route
{

    public $path;
    public $action;
    public $matches;

    //dépendances

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
    private Container $container;

    public function __construct($path, $action,$request,$environment)
    {
        $this->path = trim($path, '/');
        $this->action = $action;

        // dépendances

        $this->container = new Container();

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

    public function matches(string $url)
    {
        $path = preg_replace('#:([\w]+)#', '([^/]+)', $this->path);
        $pathToMatch = "#^$path$#";

        if (preg_match($pathToMatch,$url,$matches)) {
            $this->matches = $matches;
            return true;
        } else {
            return false;
        }
    }

    public function execute()
    {

        $params = explode('@',$this->action);

        $controllerDependancies = $this->container->get($params[0]);

        $controller = $controllerDependancies;
        $method = $params[1];

        $result = false;

        if (!array_key_exists(1,$this->matches)){
            $result = $controller->$method();
        }

        if (isset($this->matches[1])){
            $result = $controller->$method($this->matches[1]);
        }

        if (isset($this->matches[2])){
            $result = $controller->$method($this->matches[1],$this->matches[2]);
        }

        return $result;

    }
}
