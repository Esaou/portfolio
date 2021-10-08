<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Model\Entity\User;
use App\Model\Repository\UserRepository;
use App\Service\Authorization;
use App\Service\CsrfToken;
use App\Service\FormValidator\AccountValidator;
use App\Service\FormValidator\EditPostValidator;
use App\Service\Http\RedirectResponse;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Paginator;
use App\View\View;
use App\Model\Repository\PostRepository;
use App\Model\Repository\CommentRepository;

final class UserAdminController
{
    private PostRepository $postRepository;
    private CommentRepository $commentRepository;
    private UserRepository $userRepository;
    private View $view;
    private Request $request;
    private Session $session;
    private Authorization $security;
    private EditPostValidator $editPostValidator;
    private AccountValidator $accountValidator;
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
        Authorization $security,
        EditPostValidator $editPostValidator,
        AccountValidator $accountValidator,
        CsrfToken $csrf,
        Paginator $paginator,
        RedirectResponse $redirect
    ) {
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->security = $security;
        $this->editPostValidator = $editPostValidator;
        $this->accountValidator = $accountValidator;
        $this->csrf = $csrf;
        $this->paginator = $paginator;
        $this->redirect = $redirect;

        if (!$this->security->isLogged() || $this->security->loggedAs('User')) {
            $this->redirect->redirect('forbidden');
        }
    }

    public function usersList(): Response
    {
        if (!$this->security->loggedAs('Dev')) {
            $this->redirect->redirect('forbidden');
        }

        // PAGINATION

        $tableRows = $this->userRepository->countAllUsers();
        $this->paginator->paginate($tableRows, 10, 'users');
        $users = $this->userRepository->findBy(
            [],
            ['lastname' =>'asc'],
            $this->paginator->getLimit(),
            $this->paginator->getOffset()
        );

        return new Response($this->view->render([
            'template' => 'users',
            'type' => 'backoffice',
            'data' => [
                'users' => $users,
                'paginator' => $this->paginator->getPaginator()
            ],
        ]), 200);
    }

    public function userAccount(int $idUser): Response
    {
        $user = $this->userRepository->findOneBy(['id_utilisateur' => $idUser]);

        if ($this->request->getMethod() === 'POST' && $this->csrf->checkToken()) {

            /** @var array $data */
            $data = $this->request->request()->all();

            if ($this->accountValidator->validate($data)) {
                $password = password_hash($data['password'], PASSWORD_BCRYPT);
                if ($user) {
                    $user = new User(
                        $user->getIdUtilisateur(),
                        $data['firstname'],
                        $data['lastname'],
                        $data['email'],
                        $password,
                        $user->getIsValid(),
                        $user->getRole(),
                        $user->getToken()
                    );
                    $resultUpdate = $this->userRepository->update($user);

                    if ($resultUpdate) {
                        $this->session->set('user', $user);
                        $this->session->addFlashes('update', 'Vos informations sont modifiées avec succès !');
                    }

                    if (!$resultUpdate) {
                        $this->session->addFlashes('danger', 'Erreur lors de la modification !');
                    }
                }
            }
        }

        return new Response($this->view->render([
            'template' => 'userAccount',
            'type' => 'backoffice',
            'data' => [
                'token' => $this->csrf->newToken(),
                'formData' => (isset($data)) ? $data : []
            ]
        ]), 200);
    }

    public function editUser(int $idUser): Response
    {
        if (!$this->security->loggedAs('Dev')) {
            $this->redirect->redirect('forbidden');
        }

        $user = $this->userRepository->findOneBy(['id_utilisateur' => $idUser]);

        if ($this->request->getMethod() === 'POST' && $this->csrf->checkToken()) {
            $role = $this->request->request()->get('role');

            if ($user) {
                $user = new User(
                    $user->getIdUtilisateur(),
                    $user->getFirstname(),
                    $user->getLastname(),
                    $user->getEmail(),
                    $user->getPassword(),
                    $user->getIsValid(),
                    $role,
                    $user->getToken()
                );
                $resultUpdate =  $this->userRepository->update($user);

                if ($resultUpdate) {
                    $this->session->addFlashes('update', 'Utilisateur modifié avec succès !');
                    $this->redirect->redirect('users');
                }

                if (!$resultUpdate) {
                    $this->session->addFlashes('danger', 'Erreur lors de la modification !');
                }
            }
        }

        return new Response($this->view->render([

            'template' => 'editUser',
            'type' => 'backoffice',
            'data' => [
                'user' => $user,
                'token' => $this->csrf->newToken()
            ],
        ]), 200);
    }

    public function deleteUser(int $idUser): Response
    {
        if (!$this->security->loggedAs('Dev')) {
            $this->redirect->redirect('forbidden');
        }

        $user = $this->userRepository->findOneBy(['id_utilisateur' => $idUser]);

        if ($user !== null) {
            $resultDelete = $this->userRepository->delete($user);
            if ($resultDelete) {
                $this->session->addFlashes('danger', 'Utilisateur supprimé avec succès !');
            }
            if (!$resultDelete) {
                $this->session->addFlashes('danger', 'Erreur lors de la supression !');
            }
        }

        return $this->usersList();
    }
}
