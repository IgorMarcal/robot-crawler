<?php

namespace App\Services;

use App\Services\ImageToText;

class ResolveImgCaptcha
{

    private string $anticaptchakey;
       
    public  function __construct()
    {
        $this->anticaptchakey = env('ANTICAPTCHA_KEY');
    }


    public function resolve(string $pathCaptcha): array
    {
        try{
 
            $api = new ImageToText();

            $api->setKey($this->anticaptchakey);

            $api->setFile($pathCaptcha);

            $api->setSoftId(0);

            if (!$api->createTask()) {
                echo "API v2 send failed - ".$api->getErrorMessage()."\n";
                exit;
            }


            if (!$api->waitForResult()) {
                throw new \Exception($api->getErrorMessage());
            }

            unlink($pathCaptcha);
            return [
                "status"    =>  true,
                "response"  =>  $api->getTaskSolution()
            ];
        }catch(\Exception $e){
            return [
                "status"    =>  false,
                "response"  =>  $e->getMessage()
            ];
        }
    }
}