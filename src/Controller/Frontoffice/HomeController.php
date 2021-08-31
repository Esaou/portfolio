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

        if ($this->request->getMethod() === 'POST'){

            $prenom = $this->request->request()->get('firstname');
            $nom = $this->request->request()->get('lastname');
            $email = $this->request->request()->get('email');
            $content = $this->request->request()->get('content');

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


        return new Response($this->view->render([
            'template' => 'home',
        ]));
    }


}