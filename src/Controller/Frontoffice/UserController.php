<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\User;
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

    public function __construct(UserRepository $userRepository, View $view, Session $session,Request $request)
    {
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->session = $session;
        $this->request = $request;
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
        $tokenSession = $this->session->get('token');
        $tokenPost = $this->request->request()->get('token');

        if ($this->request->getMethod() === 'POST') {

            $prenom = $this->request->request()->get('firstname');
            $nom = $this->request->request()->get('lastname');
            $email = $this->request->request()->get('email');
            $password = $this->request->request()->get('password');
            $confirmPassword = $this->request->request()->get('passwordConfirm');

            $validEmail = $this->userRepository->findOneBy(['email'=>$email]);

            if (empty($nom) or empty($prenom) or empty($email) or empty($password)) {

                $this->session->addFlashes('danger', 'Tous les champs doivent être remplis !');

            } elseif ($tokenPost != $tokenSession){
                $this->session->addFlashes('danger','Token de session expiré !');
            }elseif ($confirmPassword !== $password){

                $this->session->addFlashes('danger', 'Mots de passe non identiques !');

            } elseif (strlen($prenom) < 2 or strlen($prenom) > 30 or strlen($nom) < 2 or strlen($nom) > 30) {

                $this->session->addFlashes('danger', 'Le prénom et le nom doivent contenir de 2 à 30 caractères !');

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');

            } elseif ($validEmail !== null) {

                $this->session->addFlashes('danger', 'L\'email renseigné est déjà utilisé !');

            }else {

                $token = uniqid();
                $password = password_hash($password, PASSWORD_BCRYPT);
                $user = new User(0,$prenom,$nom,$email,$password,'Non','User',$token);
                $this->userRepository->create($user);

                try{
                    $subject = "Validation de compte - Blog";
                    $to = $email;

                    $headers = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

                    $headers .= 'From: ' . 'eric.saou3@gmail.com' . "\r\n" .
                        'Reply-To: ' . 'eric.saou3@gmail.com' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

                    $content = "<p style='font-weight: bold'>Bonjour,</p>
                <p>Merci d'avoir rejoint ma communauté, pour valider votre compte cliquez sur le <span style='color: blue;font-weight: bold;'>lien</span> ci-dessous :</p>
                <a style='font-weight: bold' href='http://projet5/index.php?action=confirmUser&token=$token'>Cliquez ici</a>";

                    ini_set("SMTP", "smtp.bbox.fr");
                    ini_set("smtp_port", "25");
                    ini_set("sendmail_from", 'eric.saou3@gmail.com');


                    mail($to, $subject, $content, $headers);

                    $this->session->addFlashes('success', 'Inscription réalisée, consultez vos mails pour valider votre compte !');

                }catch (\Exception $exception){
                    $this->session->addFlashes('danger', 'Erreur lors de l\'envoi du mail !');
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
