<?php

declare(strict_types=1);

namespace  App\Controller\Backoffice;

use App\Controller\Frontoffice\SecurityController;
use App\Controller\Frontoffice\UserController;
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

final class CommentController
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
        $security = new Authorization($this->session,$this->request);

        if($security->notLogged() === true){
            new RedirectResponse('forbidden');
        }elseif($security->loggedAs('User') === true){
            new RedirectResponse('forbidden');
        }

    }

    public function commentList():Response{

        if(!is_null($this->request->query()->get('delete'))){

            $id = $this->request->query()->get('id');
            $comment = $this->commentRepository->findOneBy(['id' => $id]);

            if (!is_null($comment)){
                $this->commentRepository->delete($comment);
                $this->session->addFlashes('danger','Commentaire supprimé avec succès !');
            }

        }

        if(!is_null($this->request->query()->get('validate'))){

            $id = $this->request->query()->get('id');
            $comment = $this->commentRepository->findOneBy(['id' => $id]);

            if (!is_null($comment)){
                $comment->setIsChecked('Oui');
                $this->commentRepository->update($comment);
                $this->session->addFlashes('success','Commentaire validé avec succès !');
            }

        }

        if(!is_null($this->request->query()->get('unvalidate'))){

            $id = $this->request->query()->get('id');
            $comment = $this->commentRepository->findOneBy(['id' => $id]);
            $comment->setIsChecked('Non');
            $this->commentRepository->update($comment);

            if (!is_null($comment)){
                $comment->setIsChecked('Non');
                $this->session->addFlashes('success','Commentaire invalidé avec succès !');
            }

        }

        // PAGINATION

        $page = (int)$this->request->query()->get('page');
        $tableRows = $this->commentRepository->countAllComment();

        $paginator = (new Paginator($page,$tableRows,8))->paginate();

        $comments = $this->commentRepository->findBy([],['createdDate' =>'desc'],$paginator['parPage'],$paginator['depart']);

        return new Response($this->view->render([
            'template' => 'comments',
            'type' => 'backoffice',
            'data' => [
                'comments' => $comments,
                'pagesTotales' => $paginator['pagesTotales'],
                'pageCourante' => $paginator['pageCourante']
            ],
        ]));
    }


}
