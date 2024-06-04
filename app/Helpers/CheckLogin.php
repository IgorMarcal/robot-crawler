<?php

namespace App\Helpers;

class CheckLogin
{
    public static function checkLoginSuccess($html) : array {
        
        try{
            if (preg_match('/Dados inválidos/', $html, $matches)) {
                throw new \Exception("Could not login. " . $matches[0]);
            }

            if (preg_match('/Os caracteres digitados não correspondem à imagem. Por favor tente novamente/', $html, $matches)) {
                throw new \Exception("Could not login. " . $matches[0]);
            }
            
            if (preg_match('/Erro/', $html, $matches)) {
                var_dump($matches);
                throw new \Exception("Could not login. " . $matches[0]);
            }
            
            return [
                'erro'   => false,
                'response' => "Login realizado",
            ];
        }catch(\Exception $e){
            return [
                'erro' => true,
                'response' => $e->getMessage()
            ];
        }
    }
}