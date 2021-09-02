<?php


namespace App\Service;

class Paginator
{
    private $page;
    private $tableRows;

    public function __construct($page,$tableRows)
    {
        $this->page = $page;
        $this->tableRows = $tableRows;
    }

    public function paginate()
    {
        $parPage = 4;
        $pagesTotales = ceil($this->tableRows/$parPage);

        if(isset($this->page) AND !empty($this->page) AND $this->page > 0 AND $this->page <= $pagesTotales){
            $this->page = intval($this->page);
            $pageCourante = $this->page;
        }else{
            $pageCourante = 1;
        }

        $depart = ($pageCourante - 1)*$parPage;

        return [
            "parPage" => $parPage,
            "depart" => $depart,
            "pagesTotales" => $pagesTotales,
            "pageCourante" => $pageCourante
        ];


    }

}