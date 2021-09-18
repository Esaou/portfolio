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

    public function __construct(View $view,Request $request,Session $session,CommentRepository $commentRepository,UserRepository $userRepository,PostRepository $postRepository)
    {

        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->security = new Authorization($this->session,$this->request);
        $this->editPostValidator = new EditPostValidator($this->session);
        $this->accountValidator = new AccountValidator($this->session);
        $this->csrf = new CsrfToken($this->session,$this->request);
        $this->paginator = new Paginator($this->request,$this->view);

        if($this->security->notLogged() === true){
            new RedirectResponse('forbidden');
        }elseif($this->security->loggedAs('User') === true){
            new RedirectResponse('forbidden');
        }

    }

    public function usersList():Response{

        if($this->security->loggedAs('Dev') === false){
            new RedirectResponse('forbidden');
        }

        if(!is_null($this->request->query()->get('delete'))){

            $id = $this->request->query()->get('id');
            $comment = $this->userRepository->findOneBy(['id_utilisateur' => $id]);

            if (!is_null($comment)){
                $this->userRepository->delete($comment);
                $this->session->addFlashes('danger','Utilisateur supprimé avec succès !');
            }

        }

        // PAGINATION

        $tableRows = $this->userRepository->countAllUsers();

        $paginator = $this->paginator->paginate($tableRows,10,'users');

        $users = $this->userRepository->findBy([],['lastname' =>'asc'],$paginator['parPage'],$paginator['depart']);

        return new Response($this->view->render([
            'template' => 'users',
            'type' => 'backoffice',
            'data' => [
                'users' => $users,
                'paginator' => $paginator['paginator']
            ],
        ]),200);
    }

    public function userAccount() :Response
    {

        $id = $this->request->query()->get('id');
        $user = $this->userRepository->findOneBy(['id_utilisateur' => $id]);

        if ($this->request->getMethod() === 'POST' and $this->csrf->tokenCheck()){

            $data = $this->request->request()->all();

            if ($this->accountValidator->accountValidator($data)){

                $password = password_hash($data['password'], PASSWORD_BCRYPT);
                $user = new User($user->getIdUtilisateur(), $data['firstname'], $data['lastname'], $data['email'], $password, $user->getIsValid(), $user->getRole(), $user->getToken());
                $this->userRepository->update($user);
                $this->session->set('user', $user);
                $this->session->addFlashes('update','Vos informations sont modifiées avec succès !');
            }

        }

        return new Response($this->view->render([
            'template' => 'userAccount',
            'type' => 'backoffice',
            'data' => [
                'token' => $this->csrf->newToken()
            ]
        ]),200);
    }

    public function editUser(int $id):Response{


        if($this->security->loggedAs('Dev') === false){
            new RedirectResponse('forbidden');
        }

        $user = $this->userRepository->findOneBy(['id_utilisateur' => $id]);

        if ($this->request->getMethod() === 'POST' and $this->csrf->tokenCheck()){

            $role = $this->request->request()->get('role');

            $user = new User($user->getIdUtilisateur(),$user->getFirstname(),$user->getLastname(),$user->getEmail(),$user->getPassword(),$user->getIsValid(),$role,$user->getToken());

            $this->userRepository->update($user);

            $this->session->addFlashes('update','Utilisateur modifié avec succès !');

        }

        return new Response($this->view->render([

            'template' => 'editUser',
            'type' => 'backoffice',
            'data' => [
                'user' => $user,
                'token' => $this->csrf->newToken()
            ],
        ]),200);

    }


}
