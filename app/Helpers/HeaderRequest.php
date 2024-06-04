<?php

namespace App\Helpers;

class HeaderRequest
{
    public static function getHeader() : array {
        
        try{

            $headers = [
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Pragma: no-cache',
                'Sec-Fetch-Site: same-origin',
                'Sec-Fetch-Mode: cors',
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0',
                'sec-ch-ua: "Chromium";v="124", "Microsoft Edge";v="124", "Not-A.Brand";v="99"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Linux"',
            ];
            
            return [
                'erro'   => false,
                'response' => $headers,
            ];
        }catch(\Exception $e){
            return [
                'erro' => true,
                'response' => $e->getMessage()
            ];
        }
    }
}