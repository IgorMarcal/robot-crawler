<?php

namespace App\Helpers;

class CookieSession
{
    public static function getSessionValue($cookieFile) : array {
        
        try{
            $cookieContent = file_get_contents($cookieFile);
            if (preg_match('/JSESSIONID\s+([^\s]+)/', $cookieContent, $matches)) {
                $cookie = $matches[1];
            } else {
                throw new \Exception("JSESSIONID not found in cookie file");
            }

            return [
                'erro'   => false,
                'cookie' => $cookie,
            ];
        }catch(\Exception $e){
            return [
                'erro' => true,
                'response' => $e->getMessage()
            ];
        }
    }
}
