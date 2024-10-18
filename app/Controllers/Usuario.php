<?php

namespace App\Controllers;

use App\Models\Usuario as ModelsUsuario;

class Usuario extends ModelsUsuario
{
    public $table = 'USUARIO';
    public function init($uuid = null)
    {
        try {
            $options = $this->request->getJSON();
            $this->usuario_rede = $options?->usuario_rede;
            $this->email = $options?->email;
            $this->id_modulo = $options?->id_modulo;
            $this->matricula = $options?->matricula;
            $this->nome = $options?->nome;
            $this->perfil = json_decode($options?->perfil, true);
            $this->status = $options?->status;
            $this->request_user_id = $options?->id_usuario;
            if ($uuid) {
                $this->uuid = $uuid;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function index()
    {
        return $this->response->setJSON($this->getUsuario());
    }

    public function show($uuid = null)
    {
        return $this->response->setJSON($this->getUsuario("UUID", $uuid));
    }

    public function ValidarUsuario($usuario)
    {
        $resposta = $this->validate_user($usuario);
        return gettype($resposta) === "string" ? $this->response->setStatusCode(200, $resposta) : $this->generate_JWT(array($resposta));
    }


    public function create()
    {
        try {
            $this->init();
            if ($this->createUser())
                return $this->response->setJSON(true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update($uuid)
    {
        try {
            $this->init();
            $this->uuid = $uuid;
            $this->updatedUser();
        } catch (\Exception $e) {
            throw $e->getMessage();
        }
    }

    public function delete($uuid)
    {
        try {
            // $id_usuario = $this->getUsuario('UUID', $uuid, 'ID')[0]->ID;
            $this->id = $uuid;
            $this->deletedUser("ID", $uuid);
                // return $this->response->setStatusCode(200, 'Usuário excluído com sucesso!');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
