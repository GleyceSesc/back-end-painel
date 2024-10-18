<?php

namespace App\Controllers;

use App\Libraries\AuthLdap;
use WpOrg\Requests\Requests;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;
    protected $autorization;
    public $service;


    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    protected $data = [];
    protected $db;
    protected $token;
    protected $expire_in;
    protected $session;
    protected $model;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    public function getModel($model)
    {
        $this->model = model($model);
    }
    private function getDatabase()
    {
        return $this->db = \Config\Database::connect();
    }

    public function inserir(string $tabela, array $dados, int $id_usuario = null, int $modulo_id = null, bool $uuid, bool $log = true, bool $data = false)
    {
        $this->db = $this->getDatabase();
        try {
            if ($uuid) {
                $dados['UUID'] = $this->getUUID();
            }
            if ($data) {
                $dados['CREATED_AT'] = $this->getData();
            }
            if ($this->db->table($tabela)->insert($dados)) {
                $idInserido = $this->db->insertID();
                if ($log) {
                    $this->log_sustentação('INSERT', $id_usuario, $tabela, $modulo_id);
                }
                return $idInserido;
            }
        } catch (\Throwable $th) {
            $this->db->transRollback();
            throw new Exception("Ocorreu um erro ao inserir na tabela $tabela", 0, null);
        }
    }

    /**
     * Efetua uma atualização genérica
     *
     * @param string $tabela
     * @param array $values
     * @param array $where
     * @return integer|null total de registros removidos ou null em caso de erro
     */
    public function atualizar(string $tabela, array $values, array $where, int $id_modulo = null, int $id_usuario = null)
    {
        $this->db = $this->getDatabase();
        try {
            $dado_antigo = $this->getAll($tabela, 1, 'ASC', $where)[0];
            $builder = $this->db->table($tabela);
            $this->log_sustentação('UPDATE', $id_usuario, $tabela, $id_modulo, $dado_antigo, $values);
            return $builder->where($where)->update($values);
        } catch (\Throwable $th) {
            $this->db->transRollback();
            throw new Exception("Ocorreu um erro ao atualizar a tabela $tabela", 0, null);
        }
    }

    /**
     * Efetua remoção de registros em uma tabela
     *
     * @param string $tabela
     * @param array $where
     * @return integer|null total de registros removidos ou null em caso de erro
     */
    protected function deletar(string $tabela, array $where, int $id_modulo = null, int $id_usuario = null): ?int
    {
        $this->db = $this->getDatabase();
        try {
            if ($dado_antigo = $this->getAll($tabela, 1, 'asc', $where)) $this->log_sustentação('DELETE', $id_usuario, $tabela, $id_modulo, $dado_antigo);
            if ($this->db->table($tabela)->where($where)->delete()) {
                return $this->db->affectedRows();
            }
        } catch (\Throwable $th) {
            $this->db->transRollback();
            throw new Exception("Ocorreu um erro ao remover da tabela $tabela", 0, null);
        }
    }

    public function getId(string $tabela, int $id)
    {
        return $this->db->where('id', $id)->get($tabela)->row();
    }

    public function searchUuid(string $tabela, string $uuid)
    {
        try {
            $this->db = $this->getDatabase();
            $query = $this->db->table($tabela)->select('ID')->where('UUID', $uuid);
            $result = $query->get();
            return $result->getRow()->ID;
        } catch (\Throwable $th) {
            throw new Exception("Ocorreu um erro ao pesquisar o UUID", 0, null);
        }
    }

    /**
     * $tabela = Informar a tabela que sera consultada
     * $order_by = Informar a coluna pela qual será feita a ordenação
     * $tipo_order_by = informar se a ordenação sera ASC ou DESC
     * $condicao = informar as condições para a clausula where
     */

    public function getAll($tabela, $order_by = 1, $tipo_order_by = 'asc', array $where = null, $select = '*', $retorno = 'O')
    {
        try {
            $this->db = $this->getDatabase();
            $builder = $this->db->table($tabela)->select("DISTINCT $select", false);
            if (!empty($where)) {
                $builder->where($where);
            }
            $resultado = $builder->orderBy($order_by, $tipo_order_by)->get();
            $qtde = $resultado->getNumRows();
            if (($qtde) > 1) {
                return $resultado->getResult();
            } else {
                if ($retorno == 'O') {
                    return $resultado->getResult();
                } else {
                    return $resultado->getRowArray();
                }
            }
        } catch (\Throwable $th) {
            echo ($th->getMessage() . ' - ' . $th->getTraceAsString());
            throw new Exception("Ocorreu um erro ao pegar dados da tabela $tabela", 0, null);
        }
    }

    public function verificar(array $lista, string $procurar_dado, string $campo)
    {
        foreach ($lista as $listas) {
            return $listas->$campo == $procurar_dado ? true : false;
        }
    }
    public function log_sustentação($acao, $id_usuario, $tabela, $modulo_id, $dado_antigo = null, $dado_novo = null)
    {
        $json = json_encode([
            'dado_antigo' => $dado_antigo,
            'dado_novo' => $dado_novo,
        ]);
        switch ($acao) {
            case 'INSERT':
                $dados = array(
                    'ACAO' => $acao,
                    'USUARIO_ID' => $id_usuario,
                    'JSON' => null,
                    'TABELA' =>  $tabela,
                    'MODULO_ID' => $modulo_id,
                );
                break;
            case 'DELETE':
                $dados = array(
                    'ACAO' => $acao,
                    'USUARIO_ID' => $id_usuario,
                    'JSON' => $json,
                    'TABELA' =>  $tabela,
                    'MODULO_ID' => $modulo_id,
                );
                break;
            case 'UPDATE':
                $dados = array(
                    'ACAO' => $acao,
                    'USUARIO_ID' => $id_usuario,
                    'JSON' => $json,
                    'TABELA' =>  $tabela,
                    'MODULO_ID' => $modulo_id,
                );
                break;
        }
        $this->inserir('LOG_SUSTENTACAO', $dados, null, null, false, false);
    }

    public function getData()
    {
        date_default_timezone_set('America/Sao_Paulo');
        $data = date("Y-m-d H:i:s");
        return $data;
    }

    /**
     * Obtém o token de autenticação
     *
     * @return string Token de autenticação
     */
    public function getToken()
    {
        if (!session()->get('token') && $this->compararSegundos()) {
            $credentials = [
                'login' => getenv("LOGIN"),
                'senha' => getenv("SENHA")
            ];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/json\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($credentials)
                ]
            ];

            $context  = stream_context_create($options);
            $response = file_get_contents('https://wwws.sescmg.com.br/api/auth', false, $context);
            $responseData = json_decode($response, true);
            $this->token = $responseData["access_token"];
            session()->set('expire_in', $responseData["expire_in"]);
            session()->set('token', $responseData["access_token"]);
        }
        return session()->get('token');
    }

    protected function request(string $url, string $method, $options = null, $parameter = null, $autorization = false)
    {
        $url = $parameter ? "$url/{$parameter}" : $url;
        $headers = $this->header($autorization ? array('Content-Type', 'Authorization') : array('Content-Type'));
        $options = $options ?? [];
        if ($method === 'get')
            $request = Requests::get($url, $headers, $options);
        else if ($method === 'post')
            $request = Requests::post($url, $headers, $options);
        else if ($method === 'delete')
            $request = Requests::delete($url, $headers, $options);
        $json = json_decode($request->body ?? $request);
        return $json;
    }

    /**
     * Verifica se o token expirou
     *
     * @param int $expire_in Timestamp de expiração (opcional)
     * @return bool|null True se o token expirou, null caso contrário
     */
    public function compararSegundos($expire_in = null)
    {
        if ($expire_in === null) {
            $expire_in = session()->get('expire_in');
        }

        $expireDate = date('Y-m-d H:i:s',  $expire_in / 1000);
        $now = date('Y-m-d H:i:s');

        if ($expireDate < $now) {
            return true;
        }
    }

    public function header(array $options)
    {
        $headers = [];
        foreach ($options as $option) {
            switch ($option) {
                case 'Authorization':
                    $headers['Authorization'] = 'Bearer ' . $this->getToken();
                    break;
                case 'Content-Type':
                    $headers['Content-Type'] = 'application/json';
                    break;
            }
        }
        return $headers;
    }

    public function getUUID()
    {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }

    protected function generate_JWT($user, array $data = null)
    {
        $payload = $data ? array_merge($user, $data) : $user;
        $expire_in = 3600;
        if ($data) $payload['exp'] = time() + $expire_in;

        $token = JWT::encode($payload, '', 'HS256');
        return $token;
    }
}
