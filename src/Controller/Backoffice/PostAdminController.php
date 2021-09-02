<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Model\Repository\UserRepository;

use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Paginator;
use App\View\View;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;

final class PostAdminController
{
    private PostRepository $postRepository;
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private View $view;
    private Request $request;
    private Session $session;

    public function __construct(View $view,Request $request,Session $session,CommentRepository $commentRepository,UserRepository $userRepository,PostRepository $postRepository)
    {

        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;

    }

    public function postsList(){

        // PAGINATION

        $page = $this->request->query()->get('page');
        $tableRows = $this->postRepository->countAllPosts();

        $paginator = (new Paginator($page,$tableRows))->paginate();

        $posts = $this->postRepository->findBy([],['id' =>'desc'],$paginator['parPage'],$paginator['depart']);

        return new Response($this->view->renderAdmin([
            'template' => 'posts',
            'data' => [
                'posts' => $posts,
                'pagesTotales' => $paginator['pagesTotales'],
                'pageCourante' => $paginator['pageCourante']
            ],
        ]));
    }


}
