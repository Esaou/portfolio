<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\Comment;
use App\Model\Repository\UserRepository;
use App\Service\Database;
use App\Service\Http\Request;
use App\Service\Http\Session\Session;
use App\Service\Paginator;
use App\View\View;
use App\Service\Http\Response;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;

final class PostController
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

    public function displayOneAction(int $id): Response
    {

        // FIND A POST

        $post = $this->postRepository->findOneBy(['id' => $id]);

        // NOT FOUND

        $response = new Response($this->view->render(
            [
                'template' => 'postNotFound',
            ],
        ));

        // COMMENT FORM

        if ($this->request->getMethod() === 'POST'){

            $content = $this->request->request()->get('comment');
            $user = $this->session->get('user');

            $comment = new Comment(0,$content,$id,$user,'Non',new \DateTime('now'));

            $this->commentRepository->create($comment);

            $this->session->addFlashes('success','Commentaire posté avec succès !');


        }

        // NEXT/PREVIOUS POST

        $nextPost = $this->postRepository->nextPost($id);
        $previousPost = $this->postRepository->previousPost($id);

        // PAGINATION

        $page = $this->request->query()->get('page');
        $tableRows = $this->commentRepository->countAllCheckedComment($id);

        $paginator = (new Paginator($page,$tableRows))->paginate();

        $comments = $this->commentRepository->findBy(['post_id' => $id,'isChecked' => 'Oui'],['id' =>'desc'],$paginator['parPage'],$paginator['depart']);

        // RENDER

        if ($post !== null) {
            $response = new Response($this->view->render(
                [
                'template' => 'post',
                'data' => [
                    'post' => $post,
                    'comments' => $comments,
                    'nextPost' => $nextPost,
                    'previousPost' => $previousPost,
                    'pagesTotales' => $paginator['pagesTotales'],
                    'pageCourante' => $paginator['pageCourante']
                    ],
                ],
            ));
        }

        return $response;
    }

    public function displayAllAction(): Response
    {

        // PAGINATION

        $page = $this->request->query()->get('page');
        $tableRows = $this->postRepository->countAllPosts();

        $paginator = (new Paginator($page,$tableRows))->paginate();

        $posts = $this->postRepository->findBy([],['id' =>'desc'],$paginator['parPage'],$paginator['depart']);

        return new Response($this->view->render([
            'template' => 'posts',
            'data' => [
                'posts' => $posts,
                'pagesTotales' => $paginator['pagesTotales'],
                'pageCourante' => $paginator['pageCourante']
            ],
        ]));
    }

}
