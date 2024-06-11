<?php

namespace App\Http\Controllers;

use App\Services\Implementations\AuthServiceImplement;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $service;
    private $request;   

    public function __construct(Request $request, AuthServiceImplement $service){
        $this->service = $service;
        $this->request = $request;
    }

    function getActiveToken(){
        return $this->service->getActiveToken();
    }
    
    function login(){
        $documentNumber = $this->request->documentNumber;
        $password = $this->request->password;
        return $this->service->login($documentNumber, $password);
    }

    function logout(){
        return $this->service->logout();
    }
}
