<?php

namespace App\Http\Controllers;

use App\Enums\Destiny;
use App\Enums\ScrappingIDs;
use App\Services\CookieService;
use App\Services\ConsultService;
use App\Services\LoginService;
use App\Services\ScrappingService;
use App\Services\CaptchaImage;
use App\Http\Requests\GovSPRequest;
use Illuminate\Http\JsonResponse;

class PortalConsignadoController extends Controller
{
    public function Index(GovSPRequest $request) : JsonResponse
    {
        $retryCounter = 1;
        $maxRetries = 10;
        while($retryCounter <= $maxRetries){
            try{
                $cpf       = $request->cpf;
                $matricula = $request->matricula;
                $destino   = Destiny::from($request->destino);

                $cookie = (new CookieService())->getCookie();
                if($cookie['erro']) {
                    unlink($cookie['response']['cookieFile']);
                    throw new \Exception($cookie['response']);
                }

                $imageCaptcha = (new CaptchaImage())->getImage($cookie['response']);
                if($imageCaptcha['erro']) {
                    unlink($cookie['response']['cookieFile']);
                    throw new \Exception($imageCaptcha['response']);
                }

                $params = [
                    "imgPath"    => $imageCaptcha['response']['imgPath'],
                    "token"      => $imageCaptcha['response']['token'],
                    "cookie"     => $cookie['response']['cookieFile'],
                    "cookieFile" => $cookie['response']['cookieFile'],
                    "cookiePath" => $cookie['response']['cookiePath'],
                ];
                
                $login = (new LoginService())->PortalConsignado($params);
                if($login['erro']) {
                    unlink($cookie['response']['cookieFile']);
                    throw new \Exception($login['response']);
                }

                $params = [
                    "cookie"        => $cookie['response']['cookie'],
                    "cookieFile"    => $login['response']['cookieFile'],
                    "cookiePath"    => $login['response']['cookiePath'],
                    "pageContent"   => $login['response']['pageContent'],
                    "token"         => $imageCaptcha['response']['token'],
                    "targetConsult" => $destino->name,
                    "cpf"           => $cpf,
                    "matricula"     => $matricula,
                ];

                $consultService = (new ConsultService())->consult($params);
                unlink($cookie['response']['cookieFile']);

                $scrapping = (new ScrappingService())->getDados($consultService['response'],ScrappingIDs::from($request->destino));
                if($scrapping['erro']) {
                    $retryCounter = $maxRetries;
                    throw new \Exception("Scrapping error: " . $scrapping['response']);
                }
                return response()->json([
                    'status'   => true,
                    'message'  => $scrapping['response'],
                    'retrys'   => "Tryed " . $retryCounter . " times",
                ]);
            }catch(\Exception $e){
                if($retryCounter < $maxRetries){
                    $retryCounter++;
                    continue;
                }

                return response()->json([
                    'status'    => false,
                    'retrys'    => "Tryed " . $retryCounter . " times",
                    'message'   => $e->getMessage(),
                ]);
            }
        }
    }
}
