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

    // TODO => ne doit pas resté dans le controller, voir comment on peut en faire 
    // un service générique de validation
    private function isValidLoginForm(?array $infoUser): bool
    {
        if ($infoUser === null) {
            return false;
        }

        $user = $this->userRepository->findOneBy(['email' => $infoUser['email']]);

        if ($user === null || $infoUser['password'] !== $user->getPassword()) {
             return false;
        }

        $this->session->set('user', $user);

        return true;
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
        if ($request->getMethod() === 'POST') {
            if ($this->isValidLoginForm($request->request()->all())) {
                return new Response($this->view->renderAdmin([
                    'template' => 'posts',
                    'data' => [

                    ],
                ]),200);
            }
            $this->session->addFlashes('danger', 'Mauvais identifiants');
        }
        return new Response($this->view->render(['template' => 'login', 'data' => []]));
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

        if ($this->request->getMethod() === 'POST') {

            $prenom = $this->request->request()->get('firstname');
            $nom = $this->request->request()->get('lastname');
            $email = $this->request->request()->get('email');
            $password = $this->request->request()->get('password');
            $confirmPassword = $this->request->request()->get('passwordConfirm');

            $validEmail = $this->userRepository->findOneBy(['email'=>$email]);

            if (empty($nom) or empty($prenom) or empty($email) or empty($password)) {

                $this->session->addFlashes('danger', 'Tous les champs doivent être remplis !');

            } elseif ($confirmPassword !== $password){

                $this->session->addFlashes('danger', 'Mots de passe non identiques !');

            } elseif (strlen($prenom) < 2 or strlen($prenom) > 30 or strlen($nom) < 2 or strlen($nom) > 30) {

                $this->session->addFlashes('danger', 'Le prénom et le nom doivent contenir de 2 à 30 caractères !');

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');

            } elseif ($validEmail !== null) {

                $this->session->addFlashes('danger', 'L\'email renseigné est déjà utilisé !');

            } else {

                $token = uniqid();
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

        return new Response($this->view->render([
            'template' => 'register',
        ]));
    }

    public function confirmUser(){
        $token = $this->request->query()->get('token');
        $user = $this->userRepository->findOneBy(['token'=>$token]);
        $user->setIsValid('Oui');
        $this->userRepository->update($user);

        $this->session->addFlashes('success','Votre compte est validé succès !');

        return new Response($this->view->render([
            'template' => 'login',
        ]));
    }
}
