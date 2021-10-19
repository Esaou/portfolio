<?php

declare(strict_types=1);

namespace  App\Controller\Frontoffice;

use App\Model\Entity\Comment;
use App\Model\Entity\Post;
use App\Model\Repository\UserRepository;
use App\Service\CsrfToken;
use App\Service\FormValidator\CommentValidator;
use App\Service\Http\RedirectResponse;
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
    private CommentValidator $validator;
    private CsrfToken $csrf;
    private Paginator $paginator;
    private RedirectResponse $redirect;

    public function __construct(
        View $view,
        Request $request,
        Session $session,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        PostRepository $postRepository,
        CommentValidator $validator,
        CsrfToken $csrf,
        Paginator $paginator,
        RedirectResponse $redirectResponse,
    ) {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->validator = $validator;
        $this->csrf = $csrf;
        $this->paginator = $paginator;
        $this->redirect = $redirectResponse;
    }

    public function displayOneAction(int $idPost, int $page = 0): Response
    {

        // FIND A POST

        $post = $this->postRepository->findOneBy(['id_post' => $idPost]);

        if ($post === null) {
            $this->redirect->redirect('/postNotFound');
        }

        // COMMENT FORM

        if ($this->request->getMethod() === 'POST' && $this->csrf->checkToken()) {

            /**
* 
             *
 * @var array $data 
*/
            $data = $this->request->request()->all();

            if ($this->validator->validate($data)) {

                /**
* 
                 *
 * @var Post $post 
*/

                $user = $this->session->get('user');
                $comment = new Comment(0, $data['comment'], $post, $user, 'Non', new \DateTime('now'));
                $result = $this->commentRepository->create($comment);

                if ($result) {
                    $this->session->addFlashes('success', 'Commentaire posté avec succès !');
                }

                if (!$result) {
                    $this->session->addFlashes('danger', 'Erreur lors de la création du commentaire !');
                }
            }
        }

        // PAGINATION

        $tableRows = $this->commentRepository->countAllCheckedComment($idPost);
        $this->paginator->paginate($tableRows, 4, 'post/' . $idPost, $page);
        $comments = $this->commentRepository->findBy(
            ['post_id' => $idPost,'isChecked' => 'Oui'],
            ['createdDate' => 'desc'],
            $this->paginator->getLimit(),
            $this->paginator->getOffset()
        );

        // RENDER

        $nextPost = false;
        $previousPost = false;

        if ($post) {
            $nextPost = $this->postRepository->nextPost($post->getCreatedAt());
            $previousPost = $this->postRepository->previousPost($post->getCreatedAt());
        }

        return  new Response(
            $this->view->render(
                [
                'type' => 'frontoffice',
                'template' => 'post',
                'data' => [
                'token' => $this->csrf->newToken(),
                'post' => $post,
                'comments' => $comments,
                'nextPost' => $nextPost,
                'previousPost' => $previousPost,
                'paginator' => $this->paginator->getPaginator()
                ],
                ],
            ), 200
        );
    }

    public function displayAllAction(int $page = 0): Response
    {

        // PAGINATION

        $tableRows = $this->postRepository->countAllPosts();
        $this->paginator->paginate($tableRows, 4, 'posts',$page);
        $posts = $this->postRepository->findBy(
            [],
            ['createdAt' => 'desc'],
            $this->paginator->getLimit(),
            $this->paginator->getOffset()
        );

        return new Response(
            $this->view->render(
                [
                'template' => 'posts',
                'type' => 'frontoffice',
                'data' => [
                'posts' => $posts,
                'paginator' => $this->paginator->getPaginator()
                ],
                ]
            ), 200
        );
    }
}
