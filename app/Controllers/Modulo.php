<?php

namespace App\Controllers;

class Modulo extends BaseController
{
    public function index()
    {
        $menuData = [];
        $modulo = $this->getAll('modulos');
        foreach ($modulo as $modulos) {
            $menuData[] = ["id" => $modulos->ID,"menu_id" => $modulos->MENU_ID , "modulo" => $modulos->MODULO, "status" => $modulos->STATUS];
        }
        return $this->response->setJSON($menuData);
    }
}
