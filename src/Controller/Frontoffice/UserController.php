<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\User;
use App\Service\Authorization;
use App\Service\Mailer;
use App\Service\Validator;
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
    private Validator $validator;
    private Mailer $mailer;
    private Authorization $security;

    public function __construct(UserRepository $userRepository, View $view, Session $session,Request $request)
    {
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->request = $request;
        $this->validator = new Validator($this->session);
        $this->mailer = new Mailer();
        $this->security = new Authorization($this->session,$this->request);
    }

    public function loginAction(Request $request): Response
    {

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);

        if ($request->getMethod() === 'POST') {

            $data = $request->request()->all();

            $user = $this->userRepository->findOneBy(['email' => $data['email']]);

            $data['tokenSession'] = $this->session->get('token');
            $data['tokenPost'] = $this->request->request()->get('token');
            $data['user'] = $user;

            if ($this->validator->isValidLoginForm($data)){
                $user = $this->session->get('user');
                if ($user->getRole() === 'User'){
                    header('Location: index.php?action=home');
                }else{
                    header('Location: index.php?action=postsAdmin');
                }
            }

        }

        $this->session->set('token', $token);

        return new Response($this->view->render(['template' => 'login', 'data' => [
            'token' => $token
        ]]),403);
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

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);

        if ($this->request->getMethod() === 'POST') {

            $datas = $this->request->request()->all();

            $validEmail = $this->userRepository->findOneBy(['email'=>$datas['email']]);

            $datas['validEmail'] = $validEmail;
            $datas['tokenPost'] = $this->request->request()->get('token');
            $datas['tokenSession'] = $this->session->get('token');

            if ($this->validator->registerValidator($datas)){

                $tokenUser = uniqid();
                $password = password_hash($datas['password'], PASSWORD_BCRYPT);
                $user = new User(0,$datas['firstname'],$datas['lastname'],$datas['email'],$password,'Non','User',$tokenUser);
                $result = $this->userRepository->create($user);

                if ($result){

                    $content = "<p style='font-weight: bold'>Bonjour,</p>
                    <p>Merci d'avoir rejoint ma communauté, pour valider votre compte cliquez sur le <span style='color: blue;font-weight: bold;'>lien</span> ci-dessous :</p>
                    <a style='font-weight: bold' href='http://projet5/index.php?action=confirmUser&token=$tokenUser'>Cliquez ici</a>";

                    $result = $this->mailer->mail('Confirmation de compte','eric.saou3@gmail.com',$datas['email'],$content);

                    if ($result) {
                        $this->session->addFlashes('success', 'Inscription réalisée, consultez vos mails pour valider votre compte !');
                    } else {
                        $this->session->addFlashes('danger', 'Erreur lors de l\'envoi du mail !');
                    }
                }else{

                    $this->session->addFlashes('danger', 'Erreur lors de la création de l\'utilisateur !');

                }

            }
        }

        $this->session->set('token', $token);

        return new Response($this->view->render([
            'template' => 'register',
            'data' => [
                'token' => $token
            ],
        ]));
    }

    public function userAccount() :Response
    {

        if($this->security->notLogged() === true or $this->security->loggedAs('User') !== true){
            header('Location: index.php?action=forbidden');
        }

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);

        if ($this->request->getMethod() === 'POST'){

            $data = $this->request->request()->all();
            $data['tokenPost'] = $this->request->request()->get('token');
            $data['tokenSession'] = $this->session->get('token');

            if ($this->validator->accountValidator($data)){

                $id = $this->request->query()->get('id');
                $user = $this->userRepository->findOneBy(['id_utilisateur' => $id]);

                $password = password_hash($data['password'], PASSWORD_BCRYPT);

                $user = new User($user->getIdUtilisateur(), $data['firstname'], $data['lastname'], $data['email'], $password, $user->getIsValid(), $user->getRole(), $user->getToken());
                $this->userRepository->update($user);
                $this->session->set('user', $user);
                $this->session->addFlashes('update','Vos informations sont modifiées avec succès !');

            }
        }

        $this->session->set('token', $token);

        return new Response($this->view->render([
            'template' => 'userAccount',
            'data' => [
                'token' => $token
            ]
        ]));
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
