<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Service\CsrfToken;
use App\Service\FormValidator\ContactValidator;
use App\Service\Http\Session\Session;
use App\Service\Mailer;
use App\View\View;
use App\Service\Http\Response;
use App\Service\Http\Request;
use Dotenv\Dotenv;

final class HomeController
{
    private View $view;

    private Request $request;

    private Session $session;

    private ContactValidator $validator;

    private Mailer $mailer;

    private CsrfToken $csrf;

    public function __construct(
        View $view,
        Request $request,
        Session $session,
        ContactValidator $validator,
        CsrfToken $csrf,
        Mailer $mailer
    ) {
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->csrf = $csrf;
    }

    public function home(): Response
    {
        if ($this->request->getMethod() === 'POST' && $this->csrf->checkToken()) {

            /** @var array $data */
            $data = $this->request->request()->all();

            if ($this->validator->validate($data)) {
                $result = $this->mailer->mail(
                    'Message de ' . $data['firstname'] . ' ' . $data['lastname'],
                    $data['email'],
                    'eric.saou3@gmail.com',
                    $data,
                    'contactMail'
                );

                if ($result) {
                    $this->session->addFlashes('success', 'Message posté avec succès !');
                }
            }
        }

        return new Response($this->view->render([
            'template' => 'home',
            'type' => 'frontoffice',
            'data' => [
                'token' => $this->csrf->newToken(),
                'formData' => (isset($data)) ? $data : []
            ]
        ]), 200);
    }
}
