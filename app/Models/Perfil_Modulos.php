<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;

class Perfil_Modulos extends BaseController
{
    protected $table;
    protected $modulos;
    protected $options;
    protected $id_perfil;
    protected $status;
    protected $request_user_id;
    protected $id_modulo;
    protected $perfil;
    public function inserir_perfil_modulo(int $modulo)
    {
        $this->table = 'PERFIL_MODULO';
        return $this->inserir($this->table, [
            "MODULO_ID" => $modulo,
            "PERFIL_ID" => $this->id_perfil,
            "STATUS" => $this->status
        ], $this->request_user_id, $this->id_modulo, true, true, true);
    }

    public function process_perfil_modulo()
    {
        $this->modulos = json_decode($this->modulos, true);
        if (count($this->modulos) > 0) foreach ($this->modulos as $modulo)
            $this->inserir_perfil_modulo($modulo);
    }

    public function deletar_perfil_modulo()
    {
        $this->table = 'PERFIL_MODULO';
        $this->deletar($this->table, ['PERFIL_ID' => $this->id_perfil], $this->id_modulo, $this->request_user_id);
    }
    public function delete_perfil_Modulo()
    {
        $this->table = 'PERFIL_MODULO';
        if ($this->getAll($this->table, 1, 'asc', ["PERFIL_ID" => $this->id_perfil])) {
            $this->deletar($this->table, ['PERFIL_ID' => $this->id_perfil], $this->request_user_id, $this->id_modulo);
        }
    }
    public function get_perfil_modulo(int $perfil = null)
    {
        $this->table = 'PERFIL_MODULO';
        return $this->getAll($this->table, 1, 'ASC', ['PERFIL_ID' => $perfil ? $perfil : $this->id_perfil]);
    }
}
