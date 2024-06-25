<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\ZipServiceImplement;

class ZipController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, ZipServiceImplement $service) { 
        $this->request = $request;
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }

    function create(){
        $zone = $this->request->all();
        $userSesion = $request->user();
        $idUserSesion = $userSesion->id;
        $zone["registered_by"] = $idUserSesion;
        return $this->service->create($this->request);
    }
}
