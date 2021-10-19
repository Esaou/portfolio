<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Http\Request;
use App\View\View;

class Paginator
{
    private View $view;
    private Request $request;
    public int $parPage;
    public int $depart;
    public string $paginator;

    public function __construct(
        Request $request,
        View $view,
        int $parPage = 10,
        int $depart = 0,
        string $paginator = ''
    ) {
        $this->request = $request;
        $this->view = $view;
        $this->parPage = $parPage;
        $this->depart = $depart;
        $this->paginator = $paginator;
    }

    public function paginate(int $tableRows, int $parPage, string $route,int $page): void
    {
        $this->parPage = $parPage;

        $pagesTotales = ceil($tableRows / $parPage);
        $pageCourante = 1;

        if (!empty($page) && $page > 0 && $page <= $pagesTotales) {
            $page = (int)$page;
            $pageCourante = $page;
        }

        $this->depart = ($pageCourante - 1) * $parPage;

        $this->paginator = $this->view->render(
            [
            'type' => 'frontoffice',
            'template' => 'paginator',
            'paginator' => true,
            'data' => [
                "parPage" => $this->parPage,
                "depart" => $this->depart,
                "pagesTotales" => $pagesTotales,
                "pageCourante" => $pageCourante,
                "action" => $route
            ]
            ]
        );
    }

    public function getLimit(): int
    {
        return $this->parPage;
    }

    public function getOffset(): int
    {
        return $this->depart;
    }

    public function getPaginator(): string
    {
        return  $this->paginator;
    }
}
