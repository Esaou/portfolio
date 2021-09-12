<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\Frontoffice\SecurityController;
use App\Controller\Frontoffice\UserController;
use App\Model\Entity\User;
use App\Model\Repository\UserRepository;
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

    public function __construct(View $view,Request $request,Session $session,CommentRepository $commentRepository,UserRepository $userRepository,PostRepository $postRepository)
    {

        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->userRepository = $userRepository;
        $this->view = $view;
        $this->request = $request;
        $this->session = $session;

        $security = new SecurityController($userRepository,$this->view,$this->session,$this->request);

        if($security->notLogged() === true){
            header('Location: index.php?action=forbidden');
        }elseif($security->loggedAs('User') === true){
            header('Location: index.php?action=forbidden');
        }

    }

    public function usersList():Response{

        $userController = new UserController($this->userRepository,$this->view,$this->session,$this->request);

        if($userController->loggedAs('Dev') === false){
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
        $tokenSession = $this->session->get('token');
        $tokenPost = $this->request->request()->get('token');

        $id = $this->request->query()->get('id');
        $user = $this->userRepository->findOneBy(['id_utilisateur' => $id]);

        if ($this->request->getMethod() === 'POST'){

            $prenom = $this->request->request()->get('firstname');
            $nom = $this->request->request()->get('lastname');
            $email = $this->request->request()->get('email');
            $password = $this->request->request()->get('password');
            $confirmPassword = $this->request->request()->get('passwordConfirm');


            if (empty($nom) or empty($prenom) or empty($email) or empty($password)) {

                $this->session->addFlashes('danger', 'Tous les champs doivent être remplis !');

            } elseif ($confirmPassword !== $password){

                $this->session->addFlashes('danger', 'Mots de passe non identiques !');

            } elseif (strlen($prenom) < 2 or strlen($prenom) > 30 or strlen($nom) < 2 or strlen($nom) > 30) {

                $this->session->addFlashes('danger', 'Le prénom et le nom doivent contenir de 2 à 30 caractères !');

            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

                $this->session->addFlashes('danger', 'L\'email renseigné n\'est pas valide !');

            } elseif ($tokenPost != $tokenSession){
                $this->session->addFlashes('danger','Token de session expiré !');
            } else {

                $user = new User($user->getIdUtilisateur(), $prenom, $nom, $email, $password, $user->getIsValid(), $user->getRole(), $user->getToken());
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

        $userController = new UserController($this->userRepository,$this->view,$this->session,$this->request);

        if($userController->loggedAs('Dev') === false){
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
