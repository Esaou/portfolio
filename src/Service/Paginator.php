<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Http\Request;
use App\View\View;

class Paginator
{

    private View $view;
    private Request $request;

    public function __construct(Request $request, View $view)
    {
        $this->request = $request;
        $this->view = $view;
    }

    public function paginate(int $tableRows, int $parPage, string $route): array
    {

        $page = (int)$this->request->query()->get('page');

        $pagesTotales = ceil($tableRows/$parPage);

        if (!empty($page) && $page > 0 && $page <= $pagesTotales) {
            $page = intval($page);
            $pageCourante = $page;
        } else {
            $pageCourante = 1;
        }

        $depart = ($pageCourante - 1)*$parPage;

        $paginator = $this->view->render([
            'template' => 'paginator',
            'data' => [
                "parPage" => $parPage,
                "depart" => $depart,
                "pagesTotales" => $pagesTotales,
                "pageCourante" => $pageCourante,
                "action" => $route
            ]
        ]);

        return [
            // pour récuperer les posts selon la page dans la requete du controller
            "parPage" => $parPage,
            "depart" => $depart,
            // pour l'affichage de la pagination de le template
            "paginator" => $paginator
        ];
    }
}
