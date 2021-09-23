<?php


declare(strict_types=1);

namespace App\Service;

use App\View\View;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{

    private View $view;

    public function __construct(View $view)
    {

        $this->view = $view;
    }

    public function mail(string $subject, string $from, string $to, string $type, array $data, string $template):int
    {

        $transport = (new Swift_SmtpTransport('localhost', 1025));

        $mailer = new Swift_Mailer($transport);

        $content = $this->view->render([
            'template' => $template,
            'data' => [
                'data' => $data
            ]
        ]);

        $message = (new Swift_Message($subject))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($content, 'text/html');

        return $mailer->send($message);
    }
}
