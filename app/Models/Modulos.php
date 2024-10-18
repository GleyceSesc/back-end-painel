<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;

class Modulos extends VW_Permissao
{
    protected $id_modulo;
    public function get_all_modulos($id = null)
    {
        return $this->getAll("MODULOS", 1, 'asc', $id ? ["ID" => $id] : null);
    }

    public function getModulos($id)
    {
        return $this->get_all_modulos($id)[0];
    }

    public function get_list_modulos($usuarioId)
    {
        $vw = $this->get_permission_by_user($usuarioId);
        $listModulos = [];
        foreach ($vw as $vws) {
            $listModulos[] = ["modulo_id" => $vws->MODULO_ID, "ci_route_id" => $vws->ROUTE];
        }
        return $listModulos;
    }
}
