<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\View\View;
use App\Service\Http\Response;

final class SecurityController
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function forbidden() :Response{
        return new Response($this->view->render([
            'template' => 'forbidden'
        ]),403);
    }

}