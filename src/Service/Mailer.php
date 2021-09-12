<?php


namespace App\Service;


use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{

    public function mail($subject,$from,$to,$content){
        $transport = (new Swift_SmtpTransport('smtp.bbox.fr', 25))
            ->setUsername('saou.eric@bbox.fr')
            ->setPassword('JaaH7Lzj');

        $mailer = new Swift_Mailer($transport);

        $message = (new Swift_Message($subject))
            ->setFrom($from)
            ->setTo($to)
            ->setBody($content, 'text/html');

        $result = $mailer->send($message);

        return $result;
    }

}