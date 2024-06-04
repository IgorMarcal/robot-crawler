<?php

namespace App\Services;

class Curl
{
    protected function get(array $values):array
    {

        try{
            $ch = curl_init($values['url']);

            if(isset($values['urlCaptcha'])){
                curl_close($ch);
                $ch = curl_init($values['urlCaptcha']);
            }

            if(isset($values['headers']) && !empty($values['headers'])){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $values['headers']);
                curl_setopt($ch, CURLOPT_HEADER, true);
                
            }

            if(isset($values['method']) && !empty($values['method'])){
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $values['method']);
            }

            if(isset($values['formDataString'])){
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $values['formDataString']);
            }
            
            if(isset($values['cookieFile'])){
                curl_setopt($ch, CURLOPT_COOKIEFILE, $values['cookieFile']);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $values['cookieFile']);
            }

            if(isset($values['cookie'])){
                curl_setopt($ch, CURLOPT_COOKIE, $values['cookie']);
            }

            if(isset($values['followLocation'])){
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 100);
            }

            if(isset($values['urlRefer'])){
                curl_setopt($ch, CURLOPT_REFERER, $values['urlRefer']);
            }
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $response = curl_exec($ch);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $info = curl_getinfo($ch);

            if(isset($values['debug']) && $values['debug']){
                var_dump($info);
            }

            curl_close($ch);

            return [
                "status"       =>  true,
                "response"     =>  $response,
                "effectiveUrl" =>  $effectiveUrl
            ];

        }catch(\Exception $e){
            return [
                "status"    =>  false,
                "response"  =>  $e->getMessage()
            ];
        }
    }
}
