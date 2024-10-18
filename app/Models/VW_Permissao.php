<?php


namespace App\Models;

use App\Controllers\BaseController;

class VW_Permissao extends BaseController
{
    protected $table;
    protected $order_by; 

    public function __construct() {
        $this->table = 'VW_PERMISSOES';
        $this->order_by = 1;
    }
   
    public function show(array $where)
    {
        return $this->getAll("VW_PERMISSOES", $this->order_by, 'asc', $where);
    }

    public function get_permission_by_user($id_usuario = null){
        return $this->show(["USUARIO_ID" => $id_usuario]);
    }
}
