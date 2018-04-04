<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Session extends Model
{
    
  // Verifica se existe uma sessão
  public static function checkSession(){
    return isset($_SESSION['user_id']);
  }

  // Realiza um login, se o login e senha estiverem certos
  public static function login($email, $password){
    $user = DB::table('users')
    ->select('id', 'email', 'password')
    ->where('email', $email)
    ->get();

    if(count($user) > 0){
      $user = $user[0];
      if(password_verify($password, $user->password)){
        return $user->id;
      }
      else{
        return null;
      }
    }else{
      return null;
    }
  }

  // Acaba com a sessão de forma geral
  public static function logout(){
    // if(isset($_SESSION['user_id'])){
    //   unset($_SESSION['user_id']);
    // }
    session_unset();    
  }

  public static function cleanAll(){
    if(isset($_SESSION['cart'])){
      unset($_SESSION['cart']);
    }
    if(isset($_SESSION['order'])){
      unset($_SESSION['order']);
    }
  }

}
