<?php

namespace App\Controllers;

use App\Models\MenuModel;
use App\Models\Modulos;

class Menu extends BaseController
{
    function __construct() {}
    public function show($usuarioId)
    {
        $usuario = new Usuario();
        $menu = new MenuModel();
        $modulos = new Modulos();
        try {
            session()->set('id', $usuarioId);
            $dados = $usuario->getUsuario("ID", $usuarioId);
            $menu = $menu->generate_menu($usuarioId);
            $modulos = $modulos->get_list_modulos($usuarioId);
            if ($menu && $modulos && $dados) {
                return $this->generate_JWT($dados, ['modules' => $modulos, 'menus' => $menu]);
            }
            return $this->response->setStatusCode('400');
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function index()
    {
        $menu = new MenuModel();
        return $this->response->setJSON($menu->show_menu());
    }
}
