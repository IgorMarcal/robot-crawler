<?php

namespace App\Services;

use App\Services\Curl;
use App\Helpers\HeaderRequest;


class ConsultService extends Curl {

    private string $portalConsignadoBase;
       
    public  function __construct()
    {
        $this->portalConsignadoBase = env('URL_PORTAL_CONSIGNADO_BASE');
    }

   public function consult($values):array
   {
        try{
            $cpf = $values['cpf'];
            $matricula = $values['matricula'];

            $queryParams     = str_replace('./', '/', $values["pageContent"]["ajaxResponse"]["u"]);
            $formData = http_build_query([
                "radioGroup"     => $values['targetConsult'],
                "SECURITYTOKEN"  => $values['token'],
                "acessar"        => "true",
            ]);
            $url = $this->portalConsignadoBase.$queryParams;

            $params = [
                "url"            => $url,
                "formDataString" => $formData,
                "cookie"         => $values['cookie'],
                "cookieFile"     => $values['cookieFile'],
                "method"         => "POST",
                "followLocation" => true,
                "headers"        => ['Accept: */*'],
            ];
            $response = $this->get($params);
            $getPageContent = (new HeaderContent())->getContent($response['response']);
            $cookie = explode(';', $getPageContent['response']["cookies"]);
            $cookie = $cookie[0];

            $headers = HeaderRequest::getHeader();
            $params = [
                "url"            => $this->portalConsignadoBase."/consignatario/autenticado",
                "cookie"         => $values['cookie'],
                "cookieFile"     => $values['cookieFile'],
                "method"         => "GET",
                "followLocation" => true,
                "headers"        => $headers['response'],
            ];

            $response = $this->get($params);
            $referUrl = "Referer: ".$response['effectiveUrl'];

            $values=[
                "url"        => $this->portalConsignadoBase,
                "cookie"     => $cookie,
                "cookieFile" => $values['cookieFile'],
                "refer"      => $referUrl
            ];
            $tokenResponse = (new TokenService())->requestToken($values);

            if($tokenResponse['erro']) {
                throw new \Exception($tokenResponse['response']);
            }
            $tokenSearch = $tokenResponse['response']['token'];
            $headerToken = "SECURITYTOKEN: $tokenSearch";

            $params = [
                "url"            => $this->portalConsignadoBase.'/consignatario/pesquisarMargem',
                "cookie"         => $cookie,
                "cookieFile"     => $values['cookieFile'],
                "method"         => "GET",
                "followLocation" => true,
                "headers"        => [$referUrl]
            ];
            $response = $this->get($params);
            $referUrl = "Referer:".$response['effectiveUrl'];

            $getPageContent = (new HeaderContent())->getContent($response['response']);
            $getPageContent = $getPageContent['response'];

            $followUrl = str_replace('./', '/', $getPageContent["ajaxResponse"]["u"]);
            
            $formData = [
                "cpfServidor"       => $cpf,
                "matriculaServidor" => $matricula,
                "selectOrgao"       => "",
                "selectProduto"     => "",
                "selectEspecie"     => "",
                "SECURITYTOKEN"     => $tokenSearch,
                "botaoPesquisar"    => "1",
            ];
            $formData = http_build_query($formData);

            $params = [
                "url"            => $this->portalConsignadoBase."/consignatario/".$followUrl,
                "formDataString" => $formData,
                "cookie"         => $cookie,
                "cookieFile"     => $values['cookieFile'],
                "method"         => "POST",
                "followLocation" => true,
                "headers"        => [
                    $referUrl,
                    $headerToken,
                    'Wicket-Ajax: true',
                    'Wicket-Ajax-BaseURL: consignatario/pesquisarMargem?7',
                ],
            ];
            $response = $this->get($params);

            return [
                "erro"       =>  false,
                "response"   =>  $response['response'],
            ];

        }catch (\Exception $e){
                return [
                "erro"     =>  true,
                "response" =>  $e->getMessage()
            ];
        }
    }
}