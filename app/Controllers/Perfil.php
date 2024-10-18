<?php

namespace App\Controllers;

use App\Models\Perfil as ModelsPerfil;

class Perfil extends ModelsPerfil
{
    public function init(string $uuid = null)
    {
        try {
            $this->options = $this->request->getJSON();

            if ($uuid) {
                $this->id_perfil = $this->searchUuid("PERFIL", $uuid);
            }
            $this->status = $this->options?->status ?? null;
            $this->perfil =  $this->options?->perfil ?? null;
            $this->modulos = $this->options?->modulos ?? [];
            $this->id_modulo = $this->options?->id_modulo ?? null;
            $this->request_user_id = $this->options?->id_usuario ?? null;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index()
    {
        return $this->response->setJSON($this->getperfil());
    }

    public function show($uuid)
    {
        return $this->response->setJSON($this->getperfil("UUID", $uuid));
    }

    public function create()
    {
        try {
            $this->init();
            $this->createPerfil();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update($uuid)
    {
        try {
            $this->init($uuid);
            $this->uuid = $uuid;
            $this->updated_perfil();
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function delete($id)
    {
        try {
            $this->init();
            $this->id_perfil = $id;
            $this->delete_usuario_perfil();
            $this->delete_perfil_Modulo();
            $this->delete_perfil();
            return $this->response->setStatusCode(200, 'Perfil excluído com sucesso!');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete_usuario_perfil()
    {
        if ($this->getAll('USUARIO_PERFIL', 1, 'ASC', ["PERFIL_ID" => $this->id_perfil])) $this->deletar('USUARIO_PERFIL', ['PERFIL_ID' => $this->id_perfil], $this->id_modulo, $this->request_user_id);
    }
}
