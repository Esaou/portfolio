<?php


declare(strict_types=1);

namespace App\Service;

use App\Service\Http\Session\Session;
use App\View\View;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{
    private View $view;
    private Session $session;

    public function __construct(View $view, Session $session)
    {
        $this->view = $view;
        $this->session = $session;
    }

    public function mail(string $subject, string $from, string $to, array $data, string $template): int
    {
        $transport = (new Swift_SmtpTransport('localhost', 1025));

        $mailer = new Swift_Mailer($transport);

        $content = $this->view->render([
            'template' => $template,
            'type' => 'frontoffice',
            'data' => [
                'data' => $data
            ]
        ]);

        $message = (new Swift_Message($subject))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($content, 'text/html');

        try {
            $result = $mailer->send($message);
        } catch (\Exception $exception) {
            $result = 0;
            $this->session->addFlashes('danger', 'Erreur lors de l\'envoi du mail');
        }

        return $result;
    }
}
