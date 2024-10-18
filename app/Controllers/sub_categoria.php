<?php

namespace App\Controllers;

use App\Models\SubCategoria as SubCategoriaModels;
class sub_categoria extends SubCategoriaModels
{
    public function categoria()
    {
        return $this->response->setJSON($this->get_categoria());
    }
    public function index()
    {
        return $this->response->setJSON($this->get_sub_categoria());
    }
    public function show($id = null)
    {
        $this->id = $id;
        return $this->response->setJSON($this->get_sub_categoria());
    }
    public function create()
    {
        $this->resposta = $this->request->getJSON();
        $this->create_sub_categoria();
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON();
    }

    public function delete($id = null)
    {
        $this->id = $id;
        $this->delete_sub_categoria();
    }
}
