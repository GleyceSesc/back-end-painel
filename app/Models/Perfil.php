<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;

class Perfil extends Perfil_Modulos
{
    protected $table;
    protected $uuid;

    public function __construct()
    {
        $this->table = "PERFIL";
    }

    public function getPerfil($colunm = null, $valor = null, $select = null)
    {
        return $this->getAll($this->table, 1, 'asc', $colunm ? [$colunm => $valor] : null, $select ?? '*');
    }

    public function createPerfil()
    {
        if ($this->getPerfil("PERFIL", $this->perfil))
            return $this->response->setStatusCode(200, "Perfil já cadastrado!");

        $this->id_perfil = $this->inserir($this->table, array('PERFIL' => $this->perfil, 'STATUS' => $this->status), $this->request_user_id, $this->id_modulo, true, true, true);
        if ($this->id_perfil && $this->modulos) {
            $this->process_perfil_modulo($this->modulos);
        }
        return $this->response->setJSON(true);
    }

    public function delete_perfil()
    {
        if ($this->deletar('PERFIL', ['ID' => $this->id_perfil], $this->id_modulo, $this->request_user_id)) return $this->response->setJSON(true);
    }

    public function updated_perfil()
    {
        $this->atualizar($this->table, array("STATUS" => $this->status, "UPDATED_AT" => $this->getData()), array("UUID" => $this->uuid));
        if ($this->modulos) {
            $this->deletar_perfil_modulo();
            $this->process_perfil_modulo($this->modulos);
        }
    }
}
