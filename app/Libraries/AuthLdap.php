<?php

namespace App\Libraries;

use Exception;
use stdClass;


/**
 * Class AuthLdap
 * @package AuthLdap\Libraries
 * @author Karthikeyan C <karthikn.mca@gmail.com>
 */
class AuthLdap
{
    /**
     *  Servidor LDAP
     */
    const __LDAP_SERVER = 'ldap://10.49.1.5';

    /**
     * configuração do domínio
     */
    const __LDAP_USERNAME = '%s@sesc-mg';

    /**
     * configuração do Dn
     */
    const __LDAP_USER_BASE_DN = 'dc=sesc-mg,dc=local';

    /**
     * configuração do filtro de usuário
     */
    const __LDAP_USER_FILTER = '(&(objectClass=user)(sAMAccountName=%s))';


    public const UAC_ENABLED = [
        '512' => 'Normal Account',
        '66048' => 'Enabled, Password Doesn’t Expire'
    ];
    public const LDAP_FILTER_CHAPA =  '(&(objectClass=user)(postalCode=%s))';
    /**
     * conexão php
     *
     * @var mixed
     */
    public $__ldap_connection = null;

    /**
     * Variável que contem erro
     *
     * @var boolean|string
     */
    protected $error = false;


    public function __construct()
    {
        $this->__conectar();
    } 

    public function autenticar($usuario, $senha){
        return $this->__auth($usuario, $senha);
    }

    public function search($usuario){
        return $this->buscar($usuario);
    }

    public function login($usuario, $senha){
        return $this->logar($usuario, $senha);
    }

    public function getUsuario($usuario_rede){
        return $this->buscar($usuario_rede);
    }

    public function getDados($usuario_rede){
        return $this->__get_usuario_ad($usuario_rede);
    }
    /**
     * conecta no servidor do AD
     *
     * @return void
     */
    public function __conectar()
    {
        if (!function_exists('ldap_connect')) {
            log_message('error', 'Ad: O módulo Ldap não está instalado.');
            $this->error = "O módulo Ldap não está instalado.";
            error_log('O módulo LDAP PHP não foi localizado.');
        }

        $this->__ldap_connection = ldap_connect($this::__LDAP_SERVER);

        if ($this->__ldap_connection === false) {
            log_message('error', 'Ad: Não foi possível conectar ao servidor.');
            $this->error = "Ad: Não foi possível conectar ao servidor.";
            error_log('Ad: Não foi possível conectar ao servidor.');
        }

        ldap_set_option($this->__ldap_connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->__ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    }

    /**
     * Efetua autenticação no Servidor AD
     *
     * @param string $usuario usuário do AD
     * @param string $senha senha do usuário
     * @return boolean status da autenticação
     */
    private function __auth($usuario = null, $senha = null): bool
    {
        $usuario = empty($usuario) ? "dirsync" : $usuario;
        $senha = empty($senha) ? "S35c@GTI" : $senha;
        $bind_rdn = sprintf($this::__LDAP_USERNAME, $usuario);
        return @ldap_bind($this->__ldap_connection, $bind_rdn, $senha);
    }

    /**
     * Obtém um atributo de um usuário retornado de ldap_get_entries
     *
     * @param array $dados array retornado de ldap_get_entries
     * @param string $atributo atributo a ser localizado
     * @return string valor do atributo
     */
    private function __get_atributo_ad($dados, $atributo)
    {
        $pessoa = $dados[0];
        if (array_key_exists($atributo, $pessoa)) {
            return $pessoa[$atributo][0];
        }
        return null;
    }

    /**
     * Gera um objeto de usuário através de um retorno de ldap_get_entries
     *
     * @param array $result_ad resultado de ldap_get_entries
     * @return object|false
     */
    private function __get_usuario_ad($result_ad)
    {
        if (is_array($result_ad) && $result_ad['count'] > 0) {
            $usuario = new stdClass();
            $usuario->nome = $this->__get_atributo_ad($result_ad, 'cn');
            $usuario->matricula = $this->__get_atributo_ad($result_ad, 'postalcode');
            $usuario->nomeCurto = $this->__get_atributo_ad($result_ad, 'givenname');
            $usuario->email = $this->__get_atributo_ad($result_ad, 'mail');
            $usuario->nascimento = $this->__get_atributo_ad($result_ad, 'homephone');
            $usuario->cargo = $this->__get_atributo_ad($result_ad, 'title');
            $usuario->departamento = $this->__get_atributo_ad($result_ad, 'department');
            $usuario->unidade = $this->__get_atributo_ad($result_ad, 'physicaldeliveryofficename');
            $usuario->telefone = $this->__get_atributo_ad($result_ad, 'telephonenumber');
            $usuario->celular = $this->__get_atributo_ad($result_ad, 'mobile');
            $usuario->ativo = $this->isActive($result_ad);
            $usuario->grupos = $this->__get_grupos($result_ad);
            return $usuario;
        }
        return false;
    }

    /**
     * Obtém os grupos do qual o usuário pertence
     *
     * @param array $dados resultado de ldap_get_entries
     * @return array|false grupos que o usuário pertence
     */
    private function __get_grupos($dados)
    {
        $pessoa = $dados[0];

        if (array_key_exists('memberof', $pessoa)) {
            $grupos = $pessoa['memberof'];
            return $this->__formata_grupos($grupos);
        }
        return false;
    }

    /**
     * formata os nomes dos grupos
     *
     * @param array $grupos grupos extrados do get_grupos
     * @return array grupos com nomes formatados
     */
    private function __formata_grupos($grupos)
    {

        if (array_key_exists('count', $grupos)) {
            unset($grupos['count']);
        }
        $grupos = array_map(function ($grupo) {
            $nomes = explode(',', $grupo);
            return str_replace('CN=', "", $nomes[0]);
        }, $grupos);

        return $grupos;
    }

    /**
     * Efetua login no Ad retornando informações do usuário
     *
     * @param string $usuario usuário do AD
     * @param string $senha senha do usuário
     * @return object|null
     */
    private function buscar($usuario)
    {
        if ($this->__auth()) {
            $filter = sprintf($this::__LDAP_USER_FILTER, $usuario);
            $search = ldap_search($this->__ldap_connection, $this::__LDAP_USER_BASE_DN, $filter, []);
            $result = ldap_get_entries($this->__ldap_connection, $search);
            return $this->__get_usuario_ad($result);
        } else {
            $this->error = "Usuário ou senha inválidos.";
            return null;
        }
    }

    /**
     * Efetua login no AD com um usuário e senha específico
     *
     * @param string $usuario
     * @param string $senha
     * @return object
     */
    private function logar($usuario = null, $senha = null)
    {
        if (empty($usuario) || empty($senha)) {
            throw new Exception("Usuario e senha obrigatórios.");
        }

        if ($this->__auth($usuario, $senha)) {
            $filter = sprintf($this::__LDAP_USER_FILTER, $usuario);
            $search = ldap_search($this->__ldap_connection, $this::__LDAP_USER_BASE_DN, $filter, []);
            $result = ldap_get_entries($this->__ldap_connection, $search);
            return $this->__get_usuario_ad($result);
        } else {
            $this->error = "Usuário ou senha inválidos.";
            return $this->error;
        }
    }

    /**
     * retorna o erro da conexão com ad
     *
     * @return string erro da conexão
     */
    private function get_error()
    {
        return $this->error;
    }

    private function isActive(array $attributes): bool
    {
        $uacCode = $this->__get_atributo_ad($attributes, 'useraccountcontrol');
        return array_key_exists($uacCode, $this::UAC_ENABLED);
    }
}
