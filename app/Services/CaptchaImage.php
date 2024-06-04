<?php

namespace App\Services;

use App\Services\Curl;
use DOMDocument;


class CaptchaImage extends Curl{

    private string $portalConsignadoBase;
    private string $portalConsignadoAdm;

    public  function __construct()
    {
        $this->portalConsignadoBase = env('URL_PORTAL_CONSIGNADO_BASE');
        $this->portalConsignadoAdm = env('URL_PORTAL_CONSIGNADO_ADMINISTRATIVO');
    }

   public function getImage($values):array {

      try{

        $data = [
            'url'           => $this->portalConsignadoAdm,
            'method'        => 'GET',
            'followLocation'=> true,
            'cookie'        => $values['cookieFile'],
            'cookieFile'    => $values['cookieFile'],
        ];
        $response = $this->get($data);

        $token = (new TokenService())->getToken($response);
        $imagePath = $this->saveCaptchaImage($response, $data);
    
        return [
            "erro"    => false,
            "response"  => [
                "imgPath"   => $imagePath['imgPath'],
                "token"     => $token['response'],
            ]
        ];

      }catch (\Exception $e){
         return [
            "erro"     =>  true,
            "response" =>  $e->getMessage()
        ];
     }
   }

    private function saveCaptchaImage($htmlContent, $data) {
        try {
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML($htmlContent['response']);
            libxml_use_internal_errors(false);

            $tags = $doc->getElementsByTagName('img');
            $count = 0;

            foreach($tags as $tag)
            {
                $count++;
                if($tag->getAttribute('id') == "cipCaptchaImg")
                {
                    $siteImageCaptcha = $tag->getAttribute('src');
                    $data = [
                        'url'           => $this->portalConsignadoBase.$siteImageCaptcha,
                        'method'        => 'GET',
                        'followLocation'=> true,
                        'cookie'        => $data['cookieFile'],
                        'cookieFile'    => $data['cookieFile'],
                    ];
                    $response = $this->get($data);
    
                    if(!$response['status']){
                        throw new \Exception("Erro ao capturar imagem");
                    }
    
                    $directoryPath = getcwd().'/CaptchaImgs';
                    if (!is_dir($directoryPath)) {
                        mkdir($directoryPath, 0755, true);
                    }

                    $imagePath = $directoryPath.'/consignado_'.date('Y_m_d_H_i_s').'.png';
                    if (file_put_contents($imagePath, $response['response']) === false) {
                        throw new \Exception("Erro ao gravar imagem");
                    }
                }
            }

            return [
                "status"    => true,
                "imgPath"   => $imagePath,
            ];
        }catch (\Exception $e){
            return [
                "erro"     =>  true,
                "response" =>  $e->getMessage()
            ];
        }
    }
}