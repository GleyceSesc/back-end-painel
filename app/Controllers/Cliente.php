<?php

namespace App\Controllers;

use WpOrg\Requests\Requests;

class Cliente extends BaseController
{
    private $cnpj;
    protected $format = 'json';
    protected  $headers;
    protected $clientes = [];
    public function Upload()
    {
        $options = $this->request->getJSON();
        $request = $this->request('http://wwws.sescmg.com.br/api/converter/extract', 'post', $options->file, null, true);
        if($message = $request->message){
            return $this->response->setStatusCode(400)->setJSON(array('message' => $message));
        }
        return $this->response->setJSON($request);
    }
    public function ReceitaWs($cnpj)
    {
        $this->cnpj = $cnpj;
        $request = $this->request("https://receitaws.com.br/v1/cnpj", 'get', null,  $this->cnpj, true, array('{}'));
        return $this->response->setJSON($request);
    }
    public function create()
    {
        $options = $this->request->getJSON();
        $clientes = $this->index();
        $json = json_decode($clientes->getBody());
        $cliente = json_encode($options, true);
        if ($dado = isset($options->cnpj) ? array($options->cnpj, 'cnpj') : array($options->cpf, 'cpf')) {
            if ($this->verificar($json, $dado[0], $dado[1])) {
                return $this->response->setStatusCode(400)->setJSON(array('message' => "$dado[1] já cadastrado"));
            } else {
                var_dump($this->request('https://wwws.sescmg.com.br/api/cadun/consumidor/consultar',  'post', $cliente, null, true));
            }
        }
    }
    public function index($matricula = null)
    {

        $request = $this->request('https://wwws.sescmg.com.br/api/cadun/consumidor/consultar', 'post', '{}', null, true);
        $error = $request?->exp ?? null;
        $error = $request?->error ?? null;
        if ($error) {
            return $this->response->setJSON(array('message' => $error));
        }
        $listClientes = $request->dados->items;

        foreach ($listClientes as $lists) {
            if ($matricula) {
                if ($lists->content->matricula == $matricula) {
                    array_push($this->clientes, $lists->content);
                    break;
                }
            } else array_push($this->clientes, $lists->content);
        }
        usort($this->clientes, function ($a, $b) {
            return $a->matricula - $b->matricula;
        });
        return $this->response->setJSON($this->clientes);
    }

    public function show($matricula)
    {
        $cliente = $this->index($matricula);
        return $this->response->setJSON($cliente->getBody());
    }

    public function Editar()
    {
        $options = $this->request->getJSON();
        $options->data = $this->getData();
        var_dump($this->response->setJSON($options));

        // $this->request('https://wwws.sescmg.com.br/api/cadun/consumidor/consultar', 'post', $options, null, true);
    }

    public function Excluir($cnpj)
    {
        echo $cnpj;
        // $this->request('https://wwws.sescmg.com.br/api/cadun/consumidor/consultar', 'post', $cnpj, null, true);
    }
}
