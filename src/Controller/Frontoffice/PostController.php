<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\Comment;
use App\Model\Repository\UserRepository;
use App\Service\Http\Request;
use App\Service\Http\Session\Session;
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

        if ($this->request->getMethod() === 'POST'){

            $content = $this->request->request()->get('comment');
            $user = $this->userRepository->findOneBy(['id_utilisateur' => 1]);
            $comment = new Comment(0,$content,$id,$user,'Non',new \DateTime('now'));
            $this->commentRepository->create($comment);
            $this->session->addFlashes('success','Commentaire posté avec succès !');

        }

        $post = $this->postRepository->findOneBy(['id' => $id]);
        $comments = $this->commentRepository->findBy(['post_id' => $id]);

        $response = new Response($this->view->render(
            [
                'template' => 'postNotFound',
            ],
        ));

        if ($post !== null) {
            $response = new Response($this->view->render(
                [
                'template' => 'post',
                'data' => [
                    'post' => $post,
                    'comments' => $comments,
                    ],
                ],
            ));
        }


        return $response;
    }

    public function displayAllAction(): Response
    {
        $posts = $this->postRepository->findAll();

        return new Response($this->view->render([
            'template' => 'posts',
            'data' => ['posts' => $posts],
        ]));
    }
}
