<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Service\Http\Session\Session;
use App\View\View;
use App\Service\Http\Response;
use App\Service\Http\Request;

final class HomeController
{
    private View $view;

    private Request $request;

    private Session $session;

    public function __construct(View $view,Request $request,Session $session)
    {
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
    }

    public function home(): Response
    {

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);
        $tokenSession = $this->session->get('token');
        $tokenPost = $this->request->request()->get('token');

        if ($this->request->getMethod() === 'POST'){

            $prenom = $this->request->request()->get('firstname');
            $nom = $this->request->request()->get('lastname');
            $email = $this->request->request()->get('email');
            $content = $this->request->request()->get('content');

            if (empty($nom) or empty($prenom) or empty($email) or empty($content)){

                $this->session->addFlashes('danger','Tous les champs doivent être remplis !');

            }elseif (strlen($prenom) < 2 or strlen($prenom) > 30 or strlen($nom) < 2 or strlen($nom) > 30){

                $this->session->addFlashes('danger','Le prénom et le nom doivent contenir de 2 à 30 caractères !');

            }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){

                $this->session->addFlashes('danger','L\'email renseigné n\'est pas valide !');

            }elseif ($tokenPost != $tokenSession){
                $this->session->addFlashes('danger','Token de session expiré !');
            } else{
                $subject = "Message de ".$prenom." ".$nom;
                $to = 'eric.saou3@gmail.com';

                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

                $headers .= 'From: '.$email."\r\n".
                    'Reply-To: '.$email."\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                $content = '<p>Bonjour,</p>
                <p>Voici un nouveau message d\'un utilisateur :</p>
                <ul>
                    <li>Nom : '.$nom.'</li>
                    <li>Prénom : '.$prenom.'</li>
                    <li>Email : '.$email.'</li>
                    <li>Contenu : '.$content.'</li>
                </ul>';

                ini_set("SMTP","smtp.bbox.fr");
                ini_set("smtp_port","25");
                ini_set("sendmail_from",$email);


                mail($to, $subject, $content, $headers);

                $this->session->addFlashes('success','Message posté avec succès !');
            }

        }

        $this->session->set('token', $token);

        return new Response($this->view->render([
            'template' => 'home',
            'data' => [
                'token' => $token
            ]
        ]));
    }


}