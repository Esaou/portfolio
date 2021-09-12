<?php

declare(strict_types=1);

namespace App\Service;

class Paginator
{
    private int|null $page;
    private int $tableRows;
    private int $parPage;

    public function __construct(int|null $page,int $tableRows,int $parPage)
    {
        $this->page = $page;
        $this->tableRows = $tableRows;
        $this->parPage = $parPage;
    }

    public function paginate(): array
    {

        $pagesTotales = ceil($this->tableRows/$this->parPage);

        if(isset($this->page) AND !empty($this->page) AND $this->page > 0 AND $this->page <= $pagesTotales){
            $this->page = intval($this->page);
            $pageCourante = $this->page;
        }else{
            $pageCourante = 1;
        }

        $depart = ($pageCourante - 1)*$this->parPage;

        return [
            "parPage" => $this->parPage,
            "depart" => $depart,
            "pagesTotales" => $pagesTotales,
            "pageCourante" => $pageCourante
        ];


    }

}