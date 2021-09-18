<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\Comment;
use App\Model\Repository\UserRepository;
use App\Service\CsrfToken;
use App\Service\FormValidator\CommentValidator;
use App\Service\Http\RedirectResponse;
use App\Service\Http\Request;
use App\Service\Http\Session\Session;
use App\Service\Paginator;
use App\Service\Validator;
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
    private CommentValidator $validator;
    private CsrfToken $csrf;

    public function __construct(View $view,Request $request,Session $session,CommentRepository $commentRepository,UserRepository $userRepository,PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->validator = new CommentValidator($this->session);
        $this->csrf = new CsrfToken($this->session,$this->request);
    }

    public function displayOneAction(int $id): Response
    {
        // FIND A POST

        $post = $this->postRepository->findOneBy(['id_post' => $id]);

        // COMMENT FORM

        if ($this->request->getMethod() === 'POST' and $this->csrf->tokenCheck()){

            $data = $this->request->request()->all();

            $user = $this->session->get('user');

            if ($this->validator->commentValidator($data)){

                $comment = new Comment(0,$data['comment'],$post,$user,'Non',new \DateTime('now'));

                $this->commentRepository->create($comment);

                $this->session->addFlashes('success','Commentaire posté avec succès !');
            }

        }

        // PAGINATION

        $page = (int)$this->request->query()->get('page');
        $tableRows = $this->commentRepository->countAllCheckedComment($id);

        $paginator = (new Paginator($page,$tableRows,4))->paginate();

        $comments = $this->commentRepository->findBy(['post_id' => $id,'isChecked' => 'Oui'],['createdDate' =>'desc'],$paginator['parPage'],$paginator['depart']);

        // RENDER


        if ($post !== null) {

            $nextPost = $this->postRepository->nextPost($post->getCreatedAt());
            $previousPost = $this->postRepository->previousPost($post->getCreatedAt());

            $response = new Response($this->view->render(
                [
                'template' => 'post',
                'data' => [
                    'token' => $this->csrf->newToken(),
                    'post' => $post,
                    'comments' => $comments,
                    'nextPost' => $nextPost,
                    'previousPost' => $previousPost,
                    'pagesTotales' => $paginator['pagesTotales'],
                    'pageCourante' => $paginator['pageCourante']
                    ],
                ],
            ),200);
        }

        if (is_null($post)){
            $response = new RedirectResponse('postNotFound');
        }

        return $response;
    }

    public function displayAllAction(): Response
    {

        // PAGINATION

        $page = (int)$this->request->query()->get('page');
        $tableRows = $this->postRepository->countAllPosts();

        $paginator = (new Paginator($page,$tableRows,4))->paginate();

        $posts = $this->postRepository->findBy([],['createdAt' =>'asc'],$paginator['parPage'],$paginator['depart']);

        return new Response($this->view->render([
            'template' => 'posts',
            'data' => [
                'posts' => $posts,
                'pagesTotales' => $paginator['pagesTotales'],
                'pageCourante' => $paginator['pageCourante']
            ],
        ]),200);
    }

}
