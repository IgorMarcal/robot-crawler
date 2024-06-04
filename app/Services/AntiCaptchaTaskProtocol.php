<?php

namespace App\Application\Services;

interface AntiCaptchaTaskProtocol {
    
    public function getPostData();
    public function getTaskSolution();
    
}