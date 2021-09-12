<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\User;
use App\Service\Mailer;
use App\Service\Validator;
use App\View\View;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Model\Repository\UserRepository;

final class SecurityController
{
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private Request $request;
    private Validator $validator;
    private Mailer $mailer;

    public function __construct(UserRepository $userRepository, View $view, Session $session,Request $request)
    {
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->request = $request;
        $this->validator = new Validator($this->session);
        $this->mailer = new Mailer();
    }

    public function notLogged():bool
    {
        return is_null($this->session->get('user'));

    }

    public function loggedAs(string $role):bool
    {
        $user = $this->session->get('user');

        if ($user->getRole() == $role){
            return true;
        }
        return false;

    }

    public function forbidden() :Response{
        return new Response($this->view->render([
            'template' => 'forbidden'
        ]),403);
    }

    public function confirmUser():Response{

        $token = $this->request->query()->get('token');
        $user = $this->userRepository->findOneBy(['token'=>$token]);
        $user->setIsValid('Oui');
        $this->userRepository->update($user);

        $this->session->addFlashes('success','Votre compte est validé succès !');

        return new Response($this->view->render([
            'template' => 'login',
        ]),200);
    }

}