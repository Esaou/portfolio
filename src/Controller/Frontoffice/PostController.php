<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\Comment;
use App\Model\Repository\UserRepository;
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
    private Validator $validator;

    public function __construct(View $view,Request $request,Session $session,CommentRepository $commentRepository,UserRepository $userRepository,PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->validator = new Validator($this->session);
    }

    public function displayOneAction(int $id): Response
    {

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);

        // FIND A POST

        $post = $this->postRepository->findOneBy(['id_post' => $id]);

        // NOT FOUND

        $response = new Response($this->view->render(
            [
                'template' => 'postNotFound',
            ],
        ));

        // COMMENT FORM

        if ($this->request->getMethod() === 'POST'){

            $data = $this->request->request()->all();

            $data['tokenSession'] = $this->session->get('token');
            $data['tokenPost'] = $this->request->request()->get('token');

            $user = $this->session->get('user');

            if ($this->validator->commentValidator($data)){

                $comment = new Comment(0,$data['comment'],$post,$user,'Non',new \DateTime('now'));

                $this->commentRepository->create($comment);

                $this->session->addFlashes('success','Commentaire posté avec succès !');
            }

        }

        // NEXT/PREVIOUS POST

        $nextPost = $this->postRepository->nextPost($id);
        $previousPost = $this->postRepository->previousPost($id);

        // PAGINATION

        $page = (int)$this->request->query()->get('page');
        $tableRows = $this->commentRepository->countAllCheckedComment($id);

        $paginator = (new Paginator($page,$tableRows,4))->paginate();

        $comments = $this->commentRepository->findBy(['post_id' => $id,'isChecked' => 'Oui'],['id' =>'desc'],$paginator['parPage'],$paginator['depart']);

        // RENDER

        $this->session->set('token', $token);

        if ($post !== null) {
            $response = new Response($this->view->render(
                [
                'template' => 'post',
                'data' => [
                    'token' => $token,
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

        $page = (int)$this->request->query()->get('page');
        $tableRows = $this->postRepository->countAllPosts();

        $paginator = (new Paginator($page,$tableRows,4))->paginate();

        $posts = $this->postRepository->findBy([],['id_post' =>'desc'],$paginator['parPage'],$paginator['depart']);

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
