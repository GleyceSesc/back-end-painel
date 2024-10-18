<?php

namespace App\Controllers;

class Unidade extends BaseController
{
    public function index()
    {
        $request = $this->request('http://wwws.sescmg.com.br/api/unidades/unidade/buscar', 'get', [], null, true);
        $error = $request?->exp ?? null;
        $error = $request?->error ?? null;

        if ($error) {
            return $this->response->setJSON(array('message' => $error));
        }
        return $this->response->setJSON($request->dados);
    }

    public function show($uuid = null)
    {
        $request = $this->request("http://wwws.sescmg.com.br/api/unidades/unidade/buscar-unidades?&uuid=$uuid", 'get', [], null, true);
        return $this->response->setJSON($request->dados);
    }
    public function create()
    {
        $data = $this->request->getJSON();
        $data->createdAt = $this->getData();
        return $this->request('http://wwws.sescmg.com.br/api/unidades/unidade', 'post', $data, null, true);
    }
    public function update($uuid = null)
    {
        $options = $this->request->getJSON();
        $options->updated_at = $this->getData();
        var_dump($options);
    }

    public function delete($uuid = null)
    {
        return $this->request('http://wwws.sescmg.com.br/api/unidades/unidade/buscar', 'delete', ['uuid' => $uuid], null, true);
    }

    public function showCities()
    {
        $request = $this->request('https://api.sescmg.com.br/vissis/api/v1/configuracao/cidades/SESC/AMBOS', 'get', [], []);
        $unidade = $request->value;
        return $this->response->setJSON($unidade);
    }

    public function showUnities($cidade_id)
    {
        $unidade = [];
        $service = [1, 145];

        foreach ($service as $services) {
            $request = $this->request("https://api.sescmg.com.br/vissis/api/v1/configuracao/unidades/SESC/AMBOS/{$cidade_id}/{$services}",  "get");
            array_push($unidade,  $request->value);
        }
        return $this->response->setJSON($unidade);
    }
}
