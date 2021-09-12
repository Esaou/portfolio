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

final class UserController
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

    private function isValidLoginForm(?array $infoUser): bool
    {
        if ($infoUser === null) {
            return false;
        }

        $user = $this->userRepository->findOneBy(['email' => $infoUser['email']]);

        if ($user === null or $user->getIsValid() === 'Non') {
            return false;
        }

        if(password_verify($infoUser['password'], $user->getPassword())) {
            $this->session->set('user', $user);
            return true;
        }

        return false;

    }

    public function loginAction(Request $request): Response
    {

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);
        $tokenSession = $this->session->get('token');
        $tokenPost = $this->request->request()->get('token');

        if ($request->getMethod() === 'POST') {

            if ($tokenPost != $tokenSession){
                $this->session->addFlashes('danger','Token de session expiré !');
            } elseif (!$this->isValidLoginForm($request->request()->all())){
                $this->session->addFlashes('danger','Mauvais identifiants !');
            } else {
                $user = $this->session->get('user');
                if ($user->getRole() === 'User'){
                    header('Location: index.php?action=userAccount');
                }else{
                    header('Location: index.php?action=postsAdmin');
                }
            }

            $this->session->addFlashes('danger', 'Mauvais identifiants');
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
