<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;
use Exception;
use PHPUnit\Event\Code\Throwable;

class Usuario extends Usuario_Perfil
{

    protected $table;
    protected $primaryKey;

    public function __construct()
    {
        $this->table = "USUARIO";
        $this->primaryKey = "ID_USUARIO";
        $this->authLdap = new \App\Libraries\AuthLdap;
        $this->authLdap->__conectar();
    }
    public function getUsuario($colunm = null, $valor = null, $select = null)
    {
        return $this->getAll($this->table, 1, 'asc', $colunm ? [$colunm => $valor] : null, $select ?? '*');
    }

    public function validate_user($usuario)
    {
        try {
            $this->usuario_rede = $usuario;
            if (!$this->getUsuario("USUARIO_REDE", $this->usuario_rede)) {
                throw new \Exception('Usuário já está cadastrado no Banco de Dados');
            } else {
                return $this->authLdap->getUsuario($this->usuario_rede);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function createUser()
    {
        $data = ['USUARIO_REDE' => $this->usuario_rede, 'EMAIL' => $this->email, 'MATRICULA' => $this->matricula, 'NOME' => $this->nome, 'STATUS' => $this->status];
        $id = $this->inserir('USUARIO', $data, $this->id, $this->id_modulo, true);
        if ($this->perfil & $id) {
            $this->foreach_usuario_perfil();
        }
    }

    public function updatedUser()
    {
        if ($this->getUsuario("UUID", $this->uuid, 'STATUS')[0]->STATUS !== $this->status) {
            $this->atualizar($this->table, array("STATUS" => $this->status, "UPDATED_AT" => $this->getData()), array("UUID" => $this->uuid), $this->id_modulo, $this->request_user_id);
        }
        $this->id = $this->getUsuario('UUID', $this->uuid)[0]->ID;
        if ($this->perfil) {
            $this->delete_usuario_perfil();
            $this->foreach_usuario_perfil();
        }
    }

    public function deletedUser($colunm, $value)
    {
        if ($this->get_usuario_perfil('USUARIO_ID', $value))
            $this->delete_usuario_perfil();
        return $this->deletar($this->table, [$colunm => $value],1, 2);
    }
}
