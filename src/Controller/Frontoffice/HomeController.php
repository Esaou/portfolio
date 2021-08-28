<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\View\View;
use App\Service\Http\Response;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;

final class HomeController
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function home(): Response
    {
        return new Response($this->view->render([
            'template' => 'home',
        ]));
    }
}