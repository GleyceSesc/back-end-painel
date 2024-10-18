<?php

namespace App\Models;

use App\Controllers\BaseController;
use CodeIgniter\Model;

class Log_Acesso extends BaseController
{
    protected $table = 'LOG_ACESSO';
    
    public function inserir_log($data, $status)
    {
        $data['ERRO'] = $this->error_message($status);
        $this->inserir($this->table, $data, null, null, false, false);
    }

    protected function error_message($currentStatusCode)
    {
        $statusCodeTexts = [
            200 => 'Login Bem-Sucessido!',
            302 => 'Encontrado',
            400 => 'Usuário sem permissão!',
            401 => 'Unauthorized',
            403 => 'Usuário Inativado',
            422 => 'Dados não inseridos!',
            404 => 'Usuário não encontrado!',
            500 => 'Erro do Sevidor'
        ];
        $errorMessage = isset($statusCodeTexts[$currentStatusCode]) ? $statusCodeTexts[$currentStatusCode] : '';
        return $errorMessage;
    }
}
