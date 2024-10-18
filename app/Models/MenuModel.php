<?php

namespace App\Models;

class MenuModel extends Modulos
{
    protected $table;

    public function __construct() {
        $this->table = 'MENU';
    }
   
    public function generate_menu($usuarioId)
    {
        $this->order_by = "ORDEM_EXIBICAO";
        $vw = $this->get_permission_by_user($usuarioId);
        $menusInseridos = [];
        $listMenu = "<ul class='metismenu mm-show' id='menu'>";
        foreach ($vw as $vws) {
            if (!isset($menusInseridos[$vws->MENU_PAI])) {
                $listMenu .= "<li><a " . ($vws->MENU_PAI_ID ? 'class = "has-arrow"' : "href='{$vws->ROUTE}'") . "aria-expanded='false'><div class='menu-icon'><i id='icon' class='{$vws->ICON_CLASS}'></i></div><span class='nav-text'>{$vws->MENU_PAI}</span></a>";
                $menusInseridos[$vws->MENU_PAI] = $vws;
                if ($vws->MENU_PAI_ID) {
                    $listMenu .= $this->get_submenu($vw);
                }
                $listMenu .= "</li>";
            }
        }

        $listMenu .= "</ul>";
        return $listMenu;
    }

    public function get_submenu($vw)
    {
        $listMenu = '<ul aria-expanded="false">';
        $subMenusInseridos = [];
        foreach ($vw as $vws) {
            if ($vws->MENU_PAI_ID) {
                if (!isset($subMenusInseridos[$vws->ROUTE])) {
                    $listMenu .= "<li><a href='$vws->ROUTE'>$vws->MODULO</a></li>";
                    $subMenusInseridos[$vws->ROUTE] = true;
                }
            }
        }
        $listMenu .= '</ul>';
        return $listMenu;
    }

    function show_menu()
    {
        $menu = $this->get_menu();
        $menuData = [];
        foreach ($menu as $menus) {
            if ($menus->STATUS !== 0) {
                array_push($menuData, ["id" => $menus->ID, "menu" => $menus->MENU, "menu_pai_id" => $menus->MENU_PAI_ID]);
            }
        }
        return $menuData;
    }

    function get_menu()
    {
        return $this->getAll($this->table);
    }
}
