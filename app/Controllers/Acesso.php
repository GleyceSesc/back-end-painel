<?php

namespace App\Controllers;


use Exception;
use App\Controllers\Usuario;
use App\Models\Usuario_Perfil;

class Acesso extends BaseController
{
    protected $usuario_perfil;
    protected $id_usuario;
    protected $menu;
    protected $request;
    function __construct()
    {
        $this->usuario_perfil = new Usuario_Perfil();
        $this->menu = new Menu();
    }

    public function Login()
    {
        session()->start();
        try {
            if ($error = session()->get("error")) {
                throw new Exception($error);
            }
            $this->id_usuario = session()->get('id_usuario');
            $this->response->setStatusCode(200, 'JWT');
            return $this->response->setJSON($this->menu->show($this->id_usuario));
        } catch (\Exception $e) {
            $this->response->setBody($e->getMessage());
            $this->Logout();
        }
    }

    public function Logout()
    {
        session()->destroy();
        return $this->response->setJSON("true");
    }
}
