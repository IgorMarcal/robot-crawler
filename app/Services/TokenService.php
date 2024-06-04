<?php

namespace App\Services;

use App\Services\Curl;
use DOMDocument;


class TokenService extends Curl{

    public function getToken($values): array {
        try {

            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $htmlLoaded = $doc->loadHTML($values['response']);
    
            if (!$htmlLoaded) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                throw new \Exception($errors[0]->message);
            }
    
            libxml_use_internal_errors(false);
            $tags = $doc->getElementsByTagName('input');
    
            $token = null;
            foreach ($tags as $tag) {
                if ($tag->getAttribute('name') === 'inputToken' || $tag->getAttribute('name') === 'SECURITYTOKEN') {
                    $token = $tag->getAttribute('value');
                    break;
                }
            }
    
            if ($token === null) {
                throw new \Exception('Token não encontrado');
            }
    
            return [
                "status"   => true,
                "response" => $token,
            ];
        } catch (\Exception $e) {
            return [
                "status" => false,
                "response" => "Erro ao processar HTML: " . $e->getMessage(),
            ];
        }
    }

    public function requestToken($values): array {
        try{

            $params = [
                "url"            => $values['url'].'/csrfTokenS',
                "cookie"         => $values['cookie'],
                "method"         => "GET",
                "followLocation" => true,
                "headers"        => [
                    'Accept: */*',
                    $values['refer']
                ],
            ];

            $response = $this->get($params);

            if (preg_match('/\("SECURITYTOKEN",\s*"([^"]+)"\);/', $response['response'], $matches)) {
                $tokenSearch = $matches[1];
            }

            if ($tokenSearch === null) {
                throw new \Exception('Token not found in request');
            }

            return [
                "erro"   => false,
                "response" => [
                    "token" => $tokenSearch
                ],
            ];

        } catch (\Exception $e) {
            return [
                "erro"      => true,
                "response"  => "Erro ao processar HTML: " . $e->getMessage(),
            ];
        }
    }
    
}

