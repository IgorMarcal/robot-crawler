<?php

namespace App\Services;

use Exception;
use App\Enums\ScrappingOption;

class ScrappingService
{
    public function getDados($html, $scrappingOption)
    {
        try{
            // var_dump($html);
            if(preg_match('/<li class="feedbackerror">\s*<span>(.*?)<\/span>\s*<\/li>/', $html, $matches)) {
                throw new \Exception($matches[1]);
            }
            $data = [];
            $id = str_replace('_', '', $scrappingOption->name);
            if(preg_match('/CPF - <span id="id'.$id.'">(.*?)<\/span>/', $html, $matches)){
                $CPF = $matches[1];
            }
            $id++;

            if(preg_match('/Nome - <span id="id'.$id.'">(.*?)<\/span>/', $html, $matches)){
                $nome =$matches[1];
            }
            $id+=2;
            
            if(preg_match('/&Oacute;rg&atilde;o - <span id="id'.$id.'">(.*?)<\/span>/', $html, $matches)){
                $orgao = $matches[1];
            }
            $id++;

            if(preg_match('/Identifica&ccedil;&atilde;o - <span id="id'.$id.'">(.*?)<\/span>/', $html, $matches)){
                $matricula = $matches[1];
            }
            $id = $id == 39 ? '3a' : $id+=1;

            if(preg_match('/M&ecirc;s de Refer&ecirc;ncia da Margem - <span id="id'.$id.'">(.*?)<\/span>/', $html, $matches)){
                $mesReferencia = $matches[1];
            }
            $id = $id == '3a' ? '3b' : $id+=1;

            if(preg_match('/Data de Processamento da Pr&oacute;xima Folha - <span id="id'.$id.'">(.*?)<\/span>/', $html, $matches)){
                $dataProcessamento = $matches[1];
            }
            
            $data[] = [
                "DADOS DE PESQUISA" => [
                    "CPF" => isset($CPF) ? $CPF : "Do not found!",
                    "Nome" => isset($nome) ? $nome : "Do not found!",
                    "Orgao" => isset($orgao) ? $orgao : "Do not found!",
                    "Matricula" => isset($matricula) ? $matricula : "Do not found!",
                    "MesReferencia" => isset($mesReferencia) ? $mesReferencia : "Do not found!",
                    "DataProcessamento" => isset($dataProcessamento) ? $dataProcessamento : "Do not found!",
                ]
            ];
            $data[] = $this->getMargin($html)['response'];
            if(is_null($data)) {
                throw new \Exception("Could not get margins");
            }

            return [
                "erro"     => false,
                "response" => $data
            ];

        }catch(Exception $e){
            return [
                "erro"     => true,
                "response" => $e->getMessage()
            ];
        }
    }

    private function getMargin($data) : array {

        try{
            preg_match_all('/<span style="float: right; text-align: right;">([-\d.,]+)<\/span>/', $data, $matches);

            $values = $matches[1];
            $numElements = count($values);
            $halfElements = $numElements/2;

            $indices = ScrappingOption::from($numElements);
            $indices = $indices->getIndices();

            foreach ($values as $key => $value) {
                if($key+1 <=  ($halfElements) ){
                    $margin["Margem Bruta"][$indices[$key]] = $value;
                }else{
                    $margin["Margem Disponivel"][$indices[$key-$halfElements]] = $value;
                }
            }

            return [
                "erro" => false,
                "response" => $margin
            ];
        }catch(Exception $e){
            return [
                "erro"     => true,
                "response" => $e->getMessage()
            ];
        }
    }
}
