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
        $email = $this->request->email;
        $password = $this->request->password;
        return $this->service->login($email, $password);
    }

    function logout(){
        return $this->service->logout();
    }
}
