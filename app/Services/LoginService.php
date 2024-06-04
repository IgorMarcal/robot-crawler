<?php

namespace App\Services;

use App\Helpers\CheckLogin;
use App\Services\Curl;
use App\Services\HeaderContent;

class LoginService extends Curl{

    private string $userPortal;
    private string $passPortal;
    private string $portalConsignadoBase;
       
    public  function __construct()
    {
        $this->userPortal = env('USER_PORTAL');
        $this->passPortal = env('PASS_PORTAL');
        $this->portalConsignadoBase = env('URL_PORTAL_CONSIGNADO_BASE');
    }

   public function portalConsignado($values):array
   {
        try{
            $captcha = (new ResolveImgCaptcha)->resolve($values['imgPath']);

            if(!$captcha['status'] ) {
                throw new \Exception("Captcha Unsolved!");
            }

            $loginData = [
                "SECURITYTOKEN"       => $values['token'],
                "captchaPanel:captcha"=> $captcha['response'],
                "inputToken"          => $values['token'],
                "loginButton"         => "1",
                "senha"               => $this->passPortal,
                "trusted"             => "",
                "username"            => $this->userPortal,
                "idb_hf_0"            => "",
            ];
            $loginData = http_build_query($loginData);

            $params = [
                "url"            => $this->portalConsignadoBase."/home?1-2.IBehaviorListener.0-tabs-panel-formUserLogin-loginButton",
                "formDataString" => $loginData,
                "cookies"        => $values['cookie'],
                "cookieFile"     => $values['cookieFile'],
                "method"         => "POST",
                "followLocation" => true,
                "headers"        => [
                    'Accept: application/xml, text/xml, */*; q=0.01',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'SECURITYTOKEN:' . $values['token'],
                ],
            ];

            $response = $this->get($params);

            $checkLogin = CheckLogin::checkLoginSuccess($response['response']);
            if($checkLogin['erro']){
                throw new \Exception($checkLogin['response']);
            }
            
            $getPageContent = (new HeaderContent())->getContent($response['response']);
            if($getPageContent['erro']){
                throw new \Exception("Inable to get header content!");
            }

            return [
                "erro"       =>  false,
                "response"   => [
                    "cookieFile" =>  $values['cookieFile'],
                    "cookiePath" =>  $values['cookiePath'],
                    "pageContent"=>  $getPageContent['response']
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
