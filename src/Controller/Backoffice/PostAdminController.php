<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Model\Entity\Post;
use App\Model\Repository\UserRepository;
use App\Service\Authorization;
use App\Service\CsrfToken;
use App\Service\FormValidator\EditPostValidator;
use App\Service\Http\RedirectResponse;
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
    private EditPostValidator $validator;
    private CsrfToken $csrf;
    private RedirectResponse $redirect;
    private Paginator $paginator;
    private Authorization $security;

    public function __construct(
        View $view,
        Request $request,
        Session $session,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        PostRepository $postRepository,
        EditPostValidator $editPostValidator,
        CsrfToken $csrf,
        Paginator $paginator,
        Authorization $security,
        RedirectResponse $redirect
    ) {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->validator = $editPostValidator;
        $this->csrf = $csrf;
        $this->paginator = $paginator;
        $this->security = $security;
        $this->redirect = $redirect;


        if (!$this->security->isLogged() || $this->security->loggedAs('User')) {
            $this->redirect->redirect('/forbidden');
        }
    }

    public function postsList(int $page = 0): Response
    {
        // PAGINATION

        $tableRows = $this->postRepository->countAllPosts();

        $this->paginator->paginate($tableRows, 10, 'admin/posts',$page);

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
                'type' => 'backoffice',
                'data' => [
                'posts' => $posts,
                'paginator' => $this->paginator->getPaginator()
                ],
                ]
            ), 200
        );
    }

    public function editPost(int $idPost): Response
    {
        $post = $this->postRepository->findOneBy(['id_post' => $idPost]);
        $users = $this->userRepository->findAll();

        if ($this->request->getMethod() === 'POST' && $this->csrf->checkToken()) {

            /**
* 
             *
 * @var array $data 
*/
            $data = $this->request->request()->all();

            if ($this->validator->validate($data)) {
                $user = $this->userRepository->findOneBy(['id_utilisateur' => (int)$data['author']]);
                if ($post) {
                    $post = new Post(
                        $post->getIdPost(),
                        $data['chapo'],
                        $data['title'],
                        $data['content'],
                        $post->getCreatedAt(),
                        new \DateTime(),
                        $user
                    );

                    $this->postRepository->update($post);

                    $this->session->addFlashes('update', 'Post modifié avec succès !');

                    return $this->postsList();
                }
            }
        }

        return new Response(
            $this->view->render(
                [
                'template' => 'editPost',
                'type' => 'backoffice',
                'data' => [
                'users' => $users,
                'post' => $post,
                'token' => $this->csrf->newToken(),
                'formData' => (isset($data)) ? $data : []
                ],
                ]
            ), 200
        );
    }

    public function addPost(): Response
    {
        if ($this->request->getMethod() === 'POST' && $this->csrf->checkToken()) {

            /**
* 
             *
 * @var array $data 
*/
            $data = $this->request->request()->all();

            if ($this->validator->validate($data)) {
                $user = $this->session->get('user');

                $post = new Post(
                    0,
                    $data['chapo'],
                    $data['title'],
                    $data['content'],
                    new \DateTime('now'),
                    null,
                    $user
                );

                $this->postRepository->create($post);

                $this->session->addFlashes('success', 'Post ajouté avec succès !');

                return $this->postsList();
            }
        }

        return new Response(
            $this->view->render(
                [
                'template' => 'addPost',
                'type' => 'backoffice',
                'data' => [
                'token' => $this->csrf->newToken(),
                'formData' => (isset($data)) ? $data : []
                ]
                ]
            ), 200
        );
    }

    public function deletePost(int $idPost): Response
    {

        $post = $this->postRepository->findOneBy(['id_post' => $idPost]);

        if ($post !== null) {
            $resultDelete = $this->postRepository->delete($post);
            if ($resultDelete) {
                $this->session->addFlashes('danger', 'Post supprimé avec succès !');
            }
            if (!$resultDelete) {
                $this->session->addFlashes('danger', 'Erreur lors de la supression !');
            }
        }

        return $this->postsList();
    }
}
