<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\User;
use App\Service\Authorization;
use App\Service\CsrfToken;
use App\Service\FormValidator\AccountValidator;
use App\Service\FormValidator\LoginValidator;
use App\Service\FormValidator\RegisterValidator;
use App\Service\Http\RedirectResponse;
use App\Service\Mailer;
use App\View\View;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Model\Repository\UserRepository;

final class UserController
{
    private UserRepository $userRepository;
    private View $view;
    private Session $session;
    private Request $request;
    private LoginValidator $loginValidator;
    private RegisterValidator $registerValidator;
    private AccountValidator $accountValidator;
    private Mailer $mailer;
    private Authorization $security;
    private CsrfToken $csrf;

    public function __construct(UserRepository $userRepository, View $view, Session $session,Request $request)
    {
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->request = $request;
        $this->loginValidator = new LoginValidator($this->session);
        $this->registerValidator = new RegisterValidator($this->session);
        $this->accountValidator = new AccountValidator($this->session);
        $this->mailer = new Mailer($this->view);
        $this->security = new Authorization($this->session,$this->request);
        $this->csrf = new CsrfToken($this->session,$this->request);
    }

    public function loginAction(Request $request): Response
    {

        if($this->security->notLogged() === false){
            new RedirectResponse('home');
        }

        $data = [];

        if ($request->getMethod() === 'POST' and $this->csrf->tokenCheck()) {

            $data = $request->request()->all();

            $user = $this->userRepository->findOneBy(['email' => $data['email']]);

            $data['user'] = $user;

            if ($this->loginValidator->loginValidator($data)){
                $this->session->set('user', $data['user']);
                $user = $this->session->get('user');
                if ($user->getRole() === 'User'){
                    new RedirectResponse('home');
                }else{
                    new RedirectResponse('postsAdmin');
                }
            }

        }

        return new Response($this->view->render(['template' => 'login', 'data' => [
            'token' => $this->csrf->newToken(),
            'formData' => $data
        ]]),200);
    }

    public function logoutAction(): Response
    {
        $this->session->remove('user');
        return new Response($this->view->render([
            'template' => 'home',
            'data' => [

            ],
        ]),200);
    }

    public function register() :Response
    {

        if($this->security->notLogged() === false){
            new RedirectResponse('home');
        }

        $datas = [];

        if ($this->request->getMethod() === 'POST' and $this->csrf->tokenCheck()) {

            $datas = $this->request->request()->all();

            $validEmail = $this->userRepository->findOneBy(['email'=>$datas['email']]);

            $datas['validEmail'] = $validEmail;

            if ($this->registerValidator->registerValidator($datas)){

                // CREATE USER

                $tokenUser = uniqid();
                $password = password_hash($datas['password'], PASSWORD_BCRYPT);
                $user = new User(0,$datas['firstname'],$datas['lastname'],$datas['email'],$password,'Non','User',$tokenUser);
                $resultUser = $this->userRepository->create($user);

                // ADD TOKEN TO MAIL DATA

                $datas['token'] = $tokenUser;

                // SEND CONFIRMATION MAIL

                if ($resultUser){

                    $result = $this->mailer->mail('Confirmation de compte','eric.saou3@gmail.com',$datas['email'],'register',$datas);

                    if ($result) {
                        $this->session->addFlashes('success', 'Inscription réalisée, consultez vos mails pour valider votre compte !');
                    }
                    if (!$result){
                        $this->session->addFlashes('danger', 'Erreur lors de l\'envoi du mail !');
                    }

                }
                if (!$resultUser){
                    $this->session->addFlashes('danger', 'Erreur lors de la création de l\'utilisateur !');
                }

            }
        }


        return new Response($this->view->render([
            'template' => 'register',
            'data' => [
                'token' => $this->csrf->newToken(),
                'formData' => $datas
            ],
        ]),200);
    }

    public function userAccount() :Response
    {

        if($this->security->notLogged() === true or $this->security->loggedAs('User') !== true){
            new RedirectResponse('home');
        }

        if ($this->request->getMethod() === 'POST' and $this->csrf->tokenCheck()){

            $data = $this->request->request()->all();

            if ($this->accountValidator->accountValidator($data)){

                $id = $this->request->query()->get('id');
                $user = $this->userRepository->findOneBy(['id_utilisateur' => $id]);

                $password = password_hash($data['password'], PASSWORD_BCRYPT);

                $user = new User($user->getIdUtilisateur(), $data['firstname'], $data['lastname'], $data['email'], $password, $user->getIsValid(), $user->getRole(), $user->getToken());
                $this->userRepository->update($user);
                $this->session->set('user', $user);
                $this->session->addFlashes('update','Vos informations sont modifiées avec succès !');

            }
        }

        return new Response($this->view->render([
            'template' => 'userAccount',
            'data' => [
                'token' => $this->csrf->newToken()
            ]
        ]),200);

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
