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

    private RedirectResponse $redirect;
    private Container $container;

    public function __construct($path, $action)
    {
        $this->path = trim($path, '/');
        $this->action = $action;

        // dépendances

        $this->container = new Container();
        $this->redirect = new RedirectResponse();
    }

    public function matches(string $url)
    {
        $path = preg_replace('#:([\w]+)#', '([^/]+)', $this->path);
        $pathToMatch = "#^$path$#";

        if (preg_match($pathToMatch, $url, $matches)) {
            $this->matches = $matches;
            return true;
        } else {
            return false;
        }
    }

    public function execute()
    {

        $params = explode('@',$this->action);

        $controllerDependancies = $this->container->getController($params[0]);

        $result = $this->container->getMethod($params[0],$params[1],$controllerDependancies,$this->matches);

        return $result;

    }
}
