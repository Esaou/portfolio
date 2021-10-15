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
    private RedirectResponse $redirect;

    public function __construct(
        UserRepository $userRepository,
        View $view,
        Session $session,
        Request $request,
        LoginValidator $loginValidator,
        RegisterValidator $registerValidator,
        AccountValidator $accountValidator,
        Mailer $mailer,
        Authorization $security,
        CsrfToken $csrf,
        RedirectResponse $redirect
    ) {
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->request = $request;
        $this->loginValidator = $loginValidator;
        $this->registerValidator = $registerValidator;
        $this->accountValidator = $accountValidator;
        $this->mailer = $mailer;
        $this->security = $security;
        $this->csrf = $csrf;
        $this->redirect = $redirect;
    }

    public function loginAction(Request $request): Response
    {
        if ($this->security->isLogged()) {
            $this->redirect->redirect('home');
        }

        if ($request->getMethod() === 'POST' && $this->csrf->checkToken()) {
            $data = $request->request()->all();

            $user = '';

            if (isset($data['email'])) {
                $user = $this->userRepository->findOneBy(['email' => $data['email']]);
            }

            $data['user'] = $user;


            if ($this->loginValidator->validate($data)) {
                $this->session->set('user', $data['user']);
                $user = $this->session->get('user');
                $this->redirect->redirect('home');
            }
        }

        return new Response(
            $this->view->render(
                ['template' => 'login', 'type' => 'frontoffice', 'data' => [
                'token' => $this->csrf->newToken(),
                'formData' => (isset($data)) ? $data : []
                ]]
            ), 200
        );
    }

    public function logoutAction(): Response
    {
        $this->session->remove('user');
        return new Response(
            $this->view->render(
                [
                'type' => 'frontoffice',
                'template' => 'home',
                'data' => [

                ],
                ]
            ), 200
        );
    }

    public function register(): Response
    {
        if ($this->security->isLogged()) {
            $this->redirect->redirect('home');
        }

        $datas = [];

        if ($this->request->getMethod() === 'POST' && $this->csrf->checkToken()) {
            $datas = $this->request->request()->all();

            $validEmail = '';

            if (isset($datas['email'])) {
                $validEmail = $this->userRepository->findOneBy(['email' => $datas['email']]);
            }

            $datas['validEmail'] = $validEmail;

            if ($this->registerValidator->validate($datas)) {
                // CREATE USER

                $tokenUser = uniqid();
                $password = password_hash($datas['password'], PASSWORD_BCRYPT);
                $user = new User(
                    0,
                    $datas['firstname'],
                    $datas['lastname'],
                    $datas['email'],
                    $password,
                    'Non',
                    'User',
                    $tokenUser
                );
                $resultCreate = $this->userRepository->create($user);

                // SEND CONFIRMATION MAIL

                if ($resultCreate) {
                    $datas['token'] = $tokenUser;

                    $resultMail = $this->mailer->mail(
                        'Confirmation de compte',
                        'eric.saou3@gmail.com',
                        $datas['email'],
                        $datas,
                        'registerMail'
                    );

                    if ($resultMail) {
                        $this->session->addFlashes(
                            'success',
                            'Inscription réalisée, consultez vos mails pour valider votre compte !'
                        );
                    }
                }
                if (!$resultCreate) {
                    $this->session->addFlashes('danger', 'Erreur lors de la création de l\'utilisateur !');
                }
            }
        }


        return new Response(
            $this->view->render(
                [
                'template' => 'register',
                'type' => 'frontoffice',
                'data' => [
                'token' => $this->csrf->newToken(),
                'formData' => $datas
                ],
                ]
            ), 200
        );
    }

    public function userAccount(int $idUser): Response
    {
        if (!$this->security->loggedAs('User')) {
            $this->redirect->redirect('home');
        }

        if ($this->request->getMethod() === 'POST' && $this->csrf->checkToken()) {


            /**
* 
             *
 * @var array $data 
*/
            $data = $this->request->request()->all();

            if ($this->accountValidator->validate($data)) {
                $user = $this->userRepository->findOneBy(['id_utilisateur' => $idUser]);

                $password = password_hash($data['password'], PASSWORD_BCRYPT);

                if ($user) {
                    $user = new User(
                        $user->getIdUtilisateur(),
                        $data['firstname'],
                        $data['lastname'],
                        $data['email'],
                        $password,
                        $user->getIsValid(),
                        $user->getRole(),
                        $user->getToken()
                    );
                    $resultUpdate = $this->userRepository->update($user);

                    if ($resultUpdate) {
                        $this->session->set('user', $user);
                        $this->session->addFlashes('update', 'Vos informations sont modifiées avec succès !');
                    }

                    if (!$resultUpdate) {
                        $this->session->addFlashes('danger', 'Erreur lors de la modification !');
                    }
                }
            }
        }

        return new Response(
            $this->view->render(
                [
                'template' => 'userAccount',
                'type' => 'frontoffice',
                'data' => [
                'token' => $this->csrf->newToken(),
                'formData' => (isset($data)) ? $data : []
                ]
                ]
            ), 200
        );
    }

    public function confirmUser(): Response
    {
        $token = $this->request->query()->get('token');
        $user = $this->userRepository->findOneBy(['token' => $token]);

        if ($user) {
            $user->setIsValid('Oui');
            $this->userRepository->update($user);
            $this->session->addFlashes('success', 'Votre compte est validé succès !');
        }

        if (!$user) {
            $this->session->addFlashes('danger', 'Erreur lors de la validation du compte !');
        }

        return new Response(
            $this->view->render(
                [
                'template' => 'login',
                'type' => 'frontoffice',
                ]
            ), 200
        );
    }
}
