<?php

namespace App\Services;

use App\Helpers\CookieSession;
use App\Services\Curl;

class CookieService extends Curl{

    private string $portalConsignadoAdm;
       
    public  function __construct()
    {
        $this->portalConsignadoAdm = env('URL_PORTAL_CONSIGNADO_ADMINISTRATIVO');
    }

   public function getCookie():array {

      try{
        $cookiePath = getcwd() . '/Cookies';
        if (!is_dir($cookiePath) && !mkdir($cookiePath, 0777, true)) {
            throw new \Exception("Failed to create cookie directory");
        }
        $cookieFile = $cookiePath.'/cookie_'.date('Y_m_d_H_i_s');
        $cookie = '';

        $data = [
            'url'           => $this->portalConsignadoAdm,
            'method'        => 'GET',
            'followLocation'=> true,
            'cookie'        => $cookie,
            'cookieFile'    => $cookieFile
        ];
        
        $response = $this->get($data);
        if(!$response['status']){
            throw new \Exception($response['response']);
        }
        
        $cookie = CookieSession::getSessionValue($cookieFile);
        if(!$cookie){
            throw new \Exception("Failed to get cookie");
        }
        $cookie = "JSESSIONID={$cookie['cookie']}";
       
        return [
            "erro"       =>  false,
            "response"   =>[
                "cookieFile" =>  $cookieFile,
                "cookiePath" =>  $cookiePath,
                "cookie"     =>  $cookie,
            ]
        ];

      }catch (\Exception $e){
         return [
            "erro"     =>  true,
            "response" =>  $e->getMessage()
        ];
     }
   }
}