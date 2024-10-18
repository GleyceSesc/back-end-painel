<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;

class Usuario_Perfil extends BaseController
{
    private $table = "USUARIO_PERFIL";
    protected $authLdap;
    protected $usuario_rede;
    protected $email;
    protected $senha;
    protected $matricula;
    protected $id_modulo;
    protected $nome;
    protected $status;
    protected $uuid;
    protected $id;
    protected $usuario_perfil;
    protected $perfil;
    protected $request_user_id;

    public function get_usuario_perfil($colunm = null, $valor = null, $select = null)
    {
        return $this->getAll($this->table, 1, 'asc', $colunm ? [$colunm => $valor] : null, $select ?? '*');
    }

    public function foreach_usuario_perfil()
    {
        if (gettype($this->perfil) === 'array') {
            foreach ($this->perfil as $perfils) {
                $this->inserir_usuario_perfil($perfils);
            }
        } else {
            $this->inserir_usuario_perfil($this->perfil);
        }
    }
    public function inserir_usuario_perfil($perfil)
    {
        if (gettype($perfil) === 'integer') {
            $this->inserir($this->table, [
                'USUARIO_ID' => $this->id,
                'PERFIL_ID' => $perfil,
                'STATUS' => 1,
            ], $this->request_user_id, $this->id_modulo, false, true, true);
        }
    }
    public function delete_usuario_perfil()
    {
        $this->deletar($this->table, ['USUARIO_ID' => $this->id], $this->id_modulo, $this->request_user_id);
    }
}
