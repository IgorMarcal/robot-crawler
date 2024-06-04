<?php

namespace App\Services;

class HeaderContent {

   public function getContent($html):array
   {
        try{

            preg_match('/^Location:\s*(.+)$/mi', $html, $locationMatches);
            $location = $locationMatches[1] ?? null;

            preg_match('/Wicket\.Ajax\.ajax\((\{.+?\})\);/s', $html, $ajaxMatches);
            $ajaxObject = $ajaxMatches[1] ?? null;
            $ajaxArray = json_decode($ajaxObject, true);

            preg_match('/^Ajax-Location:\s*(.+)$/mi', $html, $locationMatches);
            $ajaxLocation = $locationMatches[1] ?? null;

            preg_match('/^Set-Cookie:\s*(.+)$/mi', $html, $locationMatches);
            $cookies = $locationMatches[1] ?? null;

            if(is_null($location) && is_null($ajaxArray) && is_null($ajaxLocation) && is_null($cookies)){
                throw new \Exception("Failed to get header content");
            }
            
            return [
                "erro"          =>  false,
                "response"      =>[
                    "ajaxResponse"  =>  $ajaxArray,
                    "location"      =>  $location,
                    "cookies"       =>  $cookies,
                    "ajaxLocation"  =>  $ajaxLocation,
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
