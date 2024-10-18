<?php

namespace App\Filters;

use App\Models\Log_Acesso;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
use App\Models\Login;

class Autenticar implements FilterInterface
{
    protected $authLdap;
    protected $usuario_rede;
    private $senha;
    protected $database;
    protected $request;
    protected $response;

    public function __construct()
    {
        $this->authLdap = new \App\Libraries\AuthLdap;
        $this->authLdap->__conectar();
        $this->request = request();        
        $this->usuario_rede = $this->request->getPost('usuario_rede');
        $this->senha = $this->request->getPost('senha');
        $this->response = response();
        $this->database = new BaseController();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
       $login =  new Login($this->authLdap, $this->usuario_rede, $this->senha);
       $login->autenticar();
    }
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $log = new Log_Acesso();
        $currentStatusCode = $response->getStatusCode();
        $data = [
            'USUARIO_ID' => session()->get('id_usuario'),
            'USUARIO' => $this->usuario_rede,
            'COD_HTTP' => $currentStatusCode,
            'CREATED_AT' => $this->database->getData(),
            'USER_AGENT' => $request->getServer('HTTP_USER_AGENT'),
            'IP' => $request->getServer('REMOTE_ADDR'),
        ];
        $log->inserir_log($data, $currentStatusCode);
    }
}
