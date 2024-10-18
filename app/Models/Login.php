<?php

namespace App\Models;

use App\Controllers\BaseController;

class Login extends BaseController
{
    protected $authLdap;
    protected $database;
    protected $usuario_rede;
    protected $senha;
    protected $response;
    protected $id_usuario;



    public function __construct($authLdap, $usuario_rede, $senha)
    {
        $this->authLdap = $authLdap;
        $this->usuario_rede = $usuario_rede;
        $this->senha = $senha;
        $this->response = service('response');
    }

    public function autenticar()
    {
        try {
            if (empty($this->usuario_rede) || empty($this->senha)) {
                $this->response->setStatusCode(422);
                throw new \Exception("Dados não inseridos!");
            } else {
                if ($this->existing_user_ad()) {
                    $existing_user = $this->existing_user_database()[0];
                    $this->permission($existing_user?->ID);
                    session()->set('id_usuario', $existing_user->ID);
                }
            }
        } catch (\Exception $e) {
            session()->set("error", $e->getMessage());
        }
    }


    public function existing_user_database()
    {
        if ($existing_user = $this->getAll('USUARIO', 1, 'asc', ["USUARIO_REDE" => $this->usuario_rede])) {
            if ($existing_user[0]->STATUS === 1) return $existing_user;
            $this->response->setStatusCode(403);
            throw new \Exception("Usuário Inativado");
        } else {
            $this->response->setStatusCode(404);
            throw new \Exception("Usuário não encontrado!");
        }
    }

    public function existing_user_ad()
    {
        $existing_ad = $this->authLdap->login($this->usuario_rede, $this->senha);
        if (gettype($existing_ad) !== 'string')
            return $existing_ad;
        $this->response->setStatusCode(401);
        throw new \Exception("$existing_ad");
    }

    public function permission($id_usuario)
    {
        $vw_permission = new VW_Permissao();
        if (!$vw_permission->get_permission_by_user($id_usuario)) {
            $this->response->setStatusCode(400);
            throw new \Exception("Usuário sem permissão!");
        }
        return true;
    }
}
