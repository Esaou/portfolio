<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\Frontoffice\SecurityController;
use App\Controller\Frontoffice\UserController;
use App\Model\Entity\User;
use App\Model\Repository\UserRepository;
use App\Service\Authorization;
use App\Service\Http\Request;
use App\Service\Http\Response;
use App\Service\Http\Session\Session;
use App\Service\Paginator;
use App\Service\Validator;
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
    private Validator $validator;

    public function __construct(View $view,Request $request,Session $session,CommentRepository $commentRepository,UserRepository $userRepository,PostRepository $postRepository)
    {

        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;
        $this->security = new Authorization($this->session,$this->request);
        $this->validator = new Validator($this->session);

        if($this->security->notLogged() === true){
            header('Location: index.php?action=forbidden');
        }elseif($this->security->loggedAs('User') === true){
            header('Location: index.php?action=forbidden');
        }

    }

    public function usersList():Response{

        if($this->security->loggedAs('Dev') === false){
            header('Location: index.php?action=forbidden');
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

        $page = (int)$this->request->query()->get('page');
        $tableRows = $this->userRepository->countAllUsers();

        $paginator = (new Paginator($page,$tableRows,8))->paginate();

        $users = $this->userRepository->findBy([],['id_utilisateur' =>'desc'],$paginator['parPage'],$paginator['depart']);

        return new Response($this->view->renderAdmin([
            'template' => 'users',
            'data' => [
                'users' => $users,
                'pagesTotales' => $paginator['pagesTotales'],
                'pageCourante' => $paginator['pageCourante']
            ],
        ]));
    }

    public function userAccount() :Response
    {

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);

        $id = $this->request->query()->get('id');
        $user = $this->userRepository->findOneBy(['id_utilisateur' => $id]);

        if ($this->request->getMethod() === 'POST'){

            $data = $this->request->request()->all();
            $data['tokenPost'] = $this->request->request()->get('token');
            $data['tokenSession'] = $this->session->get('token');

            if ($this->validator->accountValidator($data)){

                $password = password_hash($data['password'], PASSWORD_BCRYPT);
                $user = new User($user->getIdUtilisateur(), $data['firstname'], $data['lastname'], $data['email'], $password, $user->getIsValid(), $user->getRole(), $user->getToken());
                $this->userRepository->update($user);
                $this->session->set('user', $user);
                $this->session->addFlashes('update','Vos informations sont modifiées avec succès !');
            }


        }

        $this->session->set('token', $token);

        return new Response($this->view->renderAdmin([
            'template' => 'userAccount',
            'data' => [
                'token' => $token
            ]
        ]));
    }

    public function editUser(int $id):Response{

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);
        $tokenSession = $this->session->get('token');
        $tokenPost = $this->request->request()->get('token');

        if($this->security->loggedAs('Dev') === false){
            header('Location: index.php?action=forbidden');
        }

        $user = $this->userRepository->findOneBy(['id_utilisateur' => $id]);

        if ($this->request->getMethod() === 'POST'){


            if ($tokenPost != $tokenSession){
                $this->session->addFlashes('danger','Token de session expiré !');
            }else{
                $role = $this->request->request()->get('role');

                $user = new User($user->getIdUtilisateur(),$user->getFirstname(),$user->getLastname(),$user->getEmail(),$user->getPassword(),$user->getIsValid(),$role,$user->getToken());

                $this->userRepository->update($user);

                $this->session->addFlashes('update','Utilisateur modifié avec succès !');
            }
        }

        $this->session->set('token', $token);

        return new Response($this->view->renderAdmin([

            'template' => 'editUser',
            'data' => [
                'user' => $user,
                'token' => $token
            ],
        ]));

    }


}
