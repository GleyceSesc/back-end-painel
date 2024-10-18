<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;

class SubCategoria extends BaseController
{
    protected $data;
    protected $resposta;
    protected $id;


    public function __construct() {}

    public function get_categoria()
    {
        $data = $this->request('https://wwws.sescmg.com.br/api/cadun/categorias/categoria', 'get', null, null, true);
        return $data->dados;
    }
    public function get_sub_categoria()
    {
        $paramether = $this->id ? "?id=$this->id" : '/';
        $request = $this->request("https://wwws.sescmg.com.br/api/cadun/categorias/sub-categoria$paramether", 'get', null, null, true);
        $error = $request?->exp ?? null;
        $error = $request?->error ?? null;
        if(!$paramether){
            if ($error) {
                return $this->response->setJSON(array('message' => $error));
            }
            usort($request->dados, function ($a, $b) {
                return $a->id - $b->id;
            });
        }
        return $request->dados;
    }
    public function create_sub_categoria()
    {
        $data = [
            "descricao" => $this->resposta->descricao,
            "categoria_id" => $this->resposta->categoria_id,
            "status" => $this->resposta->status,
            "idade_minima" => $this->resposta->idade_minima,
            "validade" => $this->resposta->validade
        ];
        $data = json_encode($data);
        if ($this->request('https://wwws.sescmg.com.br/api/cadun/categorias/sub-categoria', 'post', $data, null, true)) {
            return $this->response->setJSON(array('message' => 'Sub categoria inserida'));
        }
    }

    public function delete_sub_categoria()
    {
        if ($data = $this->request("https://wwws.sescmg.com.br/api/cadun/categorias/sub-categoria/inativar/$this->id", 'post', null, null, true)) {
            return $this->response->setJSON(array('message' => $data->mensagem));
        }
    }

    public function updated_sub_categoria() {}
}
