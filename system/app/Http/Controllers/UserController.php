<?php

namespace App\Http\Controllers;

use Moip\Moip;
use Moip\Auth\OAuth;

use App\User;
use App\CPF;
use App\Activation;
use App\Validation;

use App\MoipClient;
use App\MoipAccount;
use App\Moip as MoipConstants;

use DB;

class UserController extends Controller
{
  protected $access_token = MoipConstants::ACCESS_TOKEN;
  protected $moip;

  public function __construct(){
    parent::__construct();
    $this->moip = new Moip(new OAuth($this->access_token), Moip::ENDPOINT_SANDBOX);
  }

  // Cadastro de novo usuario
  public function signup(){
    $data = $this->get_post();   

    // Validar confirmar senha e depois removê-lo
    if(!User::validate_password($data['user_info']->password, $data['user_info']->confirmpassword)){
      $this->return->setFailed("Senhas não são iguais.");
      return;
    }
    else{
      unset($data['user_info']->confirmpassword);
    }

    // Verificar se o email já é cadastrado.
    if(User::doesEmailExists($data['user_info']->email)){
      $this->return->setFailed("Ocorreu um erro ao realizar o cadastro, esse email já foi cadastrado.");
      return;
    }

    // Cria o name_id e fica gerando caso já exista
    do{
      $name_id = uniqid($data['user_info']->name);
      $name_id = str_ireplace(" ", "", $name_id);
    }
    while(User::isNameIdInUse($name_id));

    $data['user_info']->name_id = $name_id;

    // Inverter as datas para o formato correto de DD-MM-YYYY para YYYY-MM-DD
    $data['user_info']->birthdate = $this->transformDate($data['user_info']->birthdate);
    $data['user_info']->issue_date = $this->transformDate($data['user_info']->issue_date);

    // Verifica se o CPF é válido
    $isCpfValid = CPF::validate($data['user_info']->cpf);    
    if(!$isCpfValid){
      $this->return->setFailed("CPF inválido.");
      return; 
    }

    // Adiciona o usuário no banco de dados
    $inseriu = User::add($data);

    // Se não inseriu, retornar error
    if($inseriu < 0){
      $this->return->setFailed("Ocorreu um erro ao tentar cadastrar.");
      return;
    }
    else{
      $moip_account = new MoipAccount();
      $moip_client = new MoipClient();
      // Ambas recebem o objeto Moip e o ID do usuário adicionado para referenciar no banco de dados      
      $status_account = $moip_account->criarConta($this->moip, $inseriu);      
      $status_client = $moip_client->criarCliente($this->moip, $inseriu);
    }

    if(!Activation::generateActivationToken($inseriu, $data['user_info']->email, $data['user_info']->name)){
      $this->return->setFailed("Ocorreu um erro ao gerar seu link de  ativação.");
      return;
    }
  }

  public function signupWithMoip(){
    $data = $this->get_post();

    // Validar confirmar senha e depois removê-lo
    if(!User::validate_password($data['user_info']->password, $data['user_info']->confirmpassword)){
      $this->return->setFailed("Senhas não são iguais.");
      return;
    }
    else{
      unset($data['user_info']->confirmpassword);
    }

    // Verificar se o email já é cadastrado.
    if(User::doesEmailExists($data['user_info']->email)){
      $this->return->setFailed("Ocorreu um erro ao realizar o cadastro, esse email já foi cadastrado.");
      return;
    }

    do{
      $name_id = uniqid($data['user_info']->name);
      $name_id = str_ireplace(" ", "", $name_id);
    }
    while(User::isNameIdInUse($name_id));

    $data['user_info']->name_id = $name_id;

    // Inverter as datas para o formato correto de DD-MM-YYYY para YYYY-MM-DD
    $data['user_info']->birthdate = $this->transformDate($data['user_info']->birthdate);
    $data['user_info']->issue_date = $this->transformDate($data['user_info']->issue_date);

    // Verifica se o CPF é válido
    $isCpfValid = CPF::validate($data['user_info']->cpf);    
    if(!$isCpfValid){
      $this->return->setFailed("CPF inválido.");
      return; 
    }

    // Adiciona o usuário no banco de dados
    $inseriu = User::add($data);

    // Se não inseriu, retornar error
    if($inseriu < 0){
      $this->return->setFailed("Ocorreu um erro ao tentar cadastrar.");
      return;
    }
    else{
      $moip_account = new MoipAccount();
      $moip_client = new MoipClient();
      // Ambas recebem o objeto Moip e o ID do usuário adicionado para referenciar no banco de dados      
      $connect = new Connect(MoipConstants::REDIRECT_URL, MoipConstants::APP_ID, true, Connect::ENDPOINT_SANDBOX);
      $status_account = $moip_account->recuperarConta($connect, $inseriu, $data['code']);      
      $status_client = $moip_client->criarCliente($this->moip, $inseriu);
    }

    if(!Activation::generateActivationToken($inseriu, $data['user_info']->email, $data['user_info']->name)){
      $this->return->setFailed("Ocorreu um erro ao gerar seu link de  ativação.");
      return;
    }
  }

  // Atualizar cadastro
  public function update(){
    $data = $this->get_post();
    $data['birthdate'] = $this->transformDate($data['birthdate']);

    $alterou = User::updateUser($data);

    if(!$alterou){
      $this->return->setFailed("Ocorreu um erro ao alterar o seu cadastro.");
    }
  }

  public function activateAccount(){
    $data = $this->get_post();
    $token = $data['token'];

    if(strlen($token) <= 0){
      $this->return->setFailed("Token inválido, ocorreu um erro no envio do token de ativação, tente novamente!");
      return;
    }

    $ativado = Activation::activate($token);

    if(!$ativado){
      $this->return->setFailed("Ocorreu um erro no envio do token de ativação, tente novamente!");
      return;
    }
  }

  // Pegar usuário X
  public function getUser(){
    $data = $this->get_post();

    $usuario = User::grabUserById($data['id']);

    if($usuario == null){
      $this->return->setFailed("Nenhum usuário encontrado com este identificador.");
      exit();
    }else{
      $this->return->setObject($usuario);
    }
  }

  // Converte a data introduzida para o formato do banco de dados
  private function transformDate($string){
    $data = explode("-", $string);
    $d = mktime(0,0,0, $data[1], $data[0], $data[2]);
    $data = date("Y-m-d", $d);
    return $data;
  }
  
  public function admin(){
    $this->isLogged();
    $status = Validation::validateAdmin($_SESSION['user_id']);

    if($status){
      return;
    }
    else{
      $this->return->setFailed("Operação inválida.");
      return;
    }
  }


}
