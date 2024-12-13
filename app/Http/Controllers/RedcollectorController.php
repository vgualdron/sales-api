<?php

namespace App\Http\Controllers;
use App\Models\Redcollector;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\RedcollectorServiceImplement;

class RedcollectorController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, RedcollectorServiceImplement $service) { 
        $this->request = $request;
        $this->service = $service;
    }

    function create(){
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $data = $this->request->all();
        $data['idUserSesion'] = $idUserSesion;
        return $this->service->create($data);
    }
}
