<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Service\Http\Session\Session;
use App\Service\Mailer;
use App\Service\Validator;
use App\View\View;
use App\Service\Http\Response;
use App\Service\Http\Request;

final class HomeController
{
    private View $view;

    private Request $request;

    private Session $session;

    private Validator $validator;

    private Mailer $mailer;

    public function __construct(View $view,Request $request,Session $session)
    {
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->validator = new Validator($this->session);
        $this->mailer = new Mailer();
    }

    public function home(): Response
    {

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);
        $tokenSession = $this->session->get('token');
        $tokenPost = $this->request->request()->get('token');

        if ($this->request->getMethod() === 'POST') {

            $data = $this->request->request()->all();
            $data['tokenPost'] = $tokenPost;
            $data['tokenSession'] = $tokenSession;

            if ($this->validator->homeContactValidator($data)) {

                $content = '<p>Bonjour,</p>
                        <p>Voici un nouveau message d\'un utilisateur :</p>
                        <ul>
                            <li>Nom : ' . $data['lastname'] . '</li>
                            <li>Prénom : ' . $data['firstname'] . '</li>
                            <li>Email : ' . $data['email'] . '</li>
                            <li>Contenu : ' . $data['content'] . '</li>
                        </ul>';

                $result = $this->mailer->mail('Message de '.$data['firstname'].' '.$data['lastname'],$data['email'],'eric.saou3@gmail.com',$content);

                if ($result) {
                    $this->session->addFlashes('success', 'Message posté avec succès !');
                } else {
                    $this->session->addFlashes('danger', 'Erreur lors de l\'envoi du message !');
                }

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