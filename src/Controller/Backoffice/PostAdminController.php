<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\Frontoffice\SecurityController;
use App\Controller\Frontoffice\UserController;
use App\Model\Entity\Post;
use App\Model\Repository\UserRepository;
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

    public function postsList():Response{

        if(!is_null($this->request->query()->get('delete'))){

            $id = $this->request->query()->get('id');
            $post = $this->postRepository->findOneBy(['id_post' => $id]);

            if (!is_null($post)){
                $this->postRepository->delete($post);
                $this->session->addFlashes('danger','Post supprimé avec succès !');
            }

        }

        // PAGINATION

        $page = (int)$this->request->query()->get('page');
        $tableRows = $this->postRepository->countAllPosts();

        $paginator = (new Paginator($page,$tableRows,8))->paginate();

        $posts = $this->postRepository->findBy([],['id_post' =>'desc'],$paginator['parPage'],$paginator['depart']);

        return new Response($this->view->renderAdmin([
            'template' => 'posts',
            'data' => [
                'posts' => $posts,
                'pagesTotales' => $paginator['pagesTotales'],
                'pageCourante' => $paginator['pageCourante']
            ],
        ]));
    }

    public function editPost(int $id):Response{

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);
        $tokenSession = $this->session->get('token');
        $tokenPost = $this->request->request()->get('token');

        $post = $this->postRepository->findOneBy(['id_post' => $id]);
        $users = $this->userRepository->findAll();

        if ($this->request->getMethod() === 'POST'){

            if ($tokenPost != $tokenSession){
                $this->session->addFlashes('danger','Token de session expiré !');
            }else{
                $title = $this->request->request()->get('title');
                $chapo = $this->request->request()->get('chapo');
                $content = $this->request->request()->get('content');
                $author = $this->request->request()->get('author');

                $user = $this->userRepository->findOneBy(['id_utilisateur'=>(int)$author]);

                $post = new Post($post->getIdPost(),$chapo,$title,$content,$post->getCreatedAt(),new \DateTime(),$user);

                $this->postRepository->update($post);

                $this->session->addFlashes('update','Post modifié avec succès !');
            }

        }

        $this->session->set('token', $token);

        return new Response($this->view->renderAdmin([
            'template' => 'editPost',
            'data' => [
                'users' => $users,
                'post' => $post,
                'token' => $token
            ],
        ]));

    }

    public function addPost():Response{

        $token = base_convert(hash('sha256', time() . mt_rand()), 16, 36);
        $tokenSession = $this->session->get('token');
        $tokenPost = $this->request->request()->get('token');


        if ($this->request->getMethod() === 'POST'){

            if ($tokenPost != $tokenSession){
                $this->session->addFlashes('danger','Token de session expiré !');
            }else{
                $title = $this->request->request()->get('title');
                $chapo = $this->request->request()->get('chapo');
                $content = $this->request->request()->get('content');
                $user = $this->session->get('user');

                $post = new Post(0,$chapo,$title,$content,new \DateTime('now'),null,$user);

                $this->postRepository->create($post);

                $this->session->addFlashes('success','Post ajouté avec succès !');
            }
        }

        $this->session->set('token', $token);

        return new Response($this->view->renderAdmin([
            'template' => 'addPost',
            'data' => [
                'token' => $token
            ]
        ]));

    }

}
