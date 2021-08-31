<?php

declare(strict_types=1);

namespace App\Controller\Frontoffice;


use App\Service\Http\Request;
use App\Service\Http\Response;
use App\View\View;

class SecurityController
{

    private View $view;

    private Request $request;

    public function __construct(View $view,Request $request)
    {
        $this->view = $view;
        $this->request = $request;
    }

    public function register() :Response
    {
        return new Response($this->view->render([
            'template' => 'register',
        ]));
    }

}