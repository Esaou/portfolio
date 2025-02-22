<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Model\Entity\Comment;
use App\Model\Repository\UserRepository;
use App\Service\Authorization;
use App\Service\Http\RedirectResponse;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Paginator;
use App\View\View;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;
use function Couchbase\defaultDecoder;

final class CommentController
{
    private PostRepository $postRepository;
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private View $view;
    private Request $request;
    private Session $session;
    private Paginator $paginator;
    private RedirectResponse $redirect;
    private Authorization $security;

    public function __construct(
        View $view,
        Request $request,
        Session $session,
        CommentRepository $commentRepository,
        UserRepository $userRepository,
        PostRepository $postRepository,
        Paginator $paginator,
        Authorization $security,
        RedirectResponse $redirectResponse
    ) {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->paginator = $paginator;
        $this->security = $security;
        $this->redirect = $redirectResponse;

        if (!$this->security->isLogged() || $this->security->loggedAs('User')) {
            $this->redirect->redirect('/forbidden');
        }
    }

    public function commentList(int $page = 0): Response
    {

        // PAGINATION

        $tableRows = $this->commentRepository->countAllComment();

        $this->paginator->paginate($tableRows, 10, 'admin/comments', $page);

        $comments = $this->commentRepository->findBy(
            [],
            ['createdDate' => 'desc'],
            $this->paginator->getLimit(),
            $this->paginator->getOffset()
        );

        return new Response(
            $this->view->render(
                [
                'template' => 'comments',
                'type' => 'backoffice',
                'data' => [
                'comments' => $comments,
                'paginator' => $this->paginator->getPaginator()
                ],
                ]
            )
        );
    }

    public function deleteComment(string $slugComment): Response
    {
        $comment = $this->commentRepository->findOneBy(['slugComment' => $slugComment]);

        if ($comment !== null) {
            $resultDelete = $this->commentRepository->delete($comment);
            if ($resultDelete) {
                $this->session->addFlashes('danger', 'Commentaire supprimé avec succès !');
            }
            if (!$resultDelete) {
                $this->session->addFlashes('danger', 'Erreur lors de la suppression !');
            }
        }

        return $this->commentList();
    }

    public function validateComment(string $slugComment): Response
    {
        $comment = $this->commentRepository->findOneBy(['slugComment' => $slugComment]);

        if ($comment !== null) {
            $comment->setIsChecked('Oui');
            $resultUpdate = $this->commentRepository->update($comment);
            if ($resultUpdate) {
                $this->session->addFlashes('success', 'Commentaire validé avec succès !');
            }
            if (!$resultUpdate) {
                $this->session->addFlashes('danger', 'Erreur lors de la modification !');
            }
        }
        return $this->commentList();
    }

    public function unvalidateComment(string $slugComment): Response
    {
        $comment = $this->commentRepository->findOneBy(['slugComment' => $slugComment]);

        if ($comment !== null) {
            $comment->setIsChecked('Non');
            $resultUpdate = $this->commentRepository->update($comment);
            if ($resultUpdate) {
                $this->session->addFlashes('danger', 'Commentaire invalidé avec succès !');
            }
            if (!$resultUpdate) {
                $this->session->addFlashes('danger', 'Erreur lors de la modification !');
            }
        }

        return $this->commentList();
    }
}
