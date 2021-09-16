<?php


namespace App\Service;

use App\View\View;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{

    private View $view;

    public function __construct(View $view){

        $this->view = $view;

    }

    public function mail($subject,$from,$to,$type,$data){

        $transport = (new Swift_SmtpTransport('localhost', 1025));

        $mailer = new Swift_Mailer($transport);

        $content = '';

        if ($type == 'contact'){
            $content = $this->view->render([
                'template' => 'contactMail',
                'data' => [
                    'data' => $data
                ]
            ]);
        }

        if ($type == 'register'){
            $content = $this->view->render([
                'template' => 'registerMail',
                'data' => [
                    'data' => $data
                ]
            ]);
        }

        $message = (new Swift_Message($subject))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($content, 'text/html');

        return $mailer->send($message);

    }

}