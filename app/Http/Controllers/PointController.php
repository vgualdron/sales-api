<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\PointServiceImplement;

class PointController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, PointServiceImplement $service) {
            $this->request = $request;
            $this->service = $service;
    }

    function list(string $status){
        return $this->service->list($status);
    }

    function listByUserSession(string $status){
        $idUserSesion = $this->request->user()->id;
        return $this->service->listByUserSession($status, $idUserSesion);
    }

    function create(){
        $data = $this->request->all();
        $idUserSesion = $this->request->user()->id;
        $data["idUserSesion"] = $idUserSesion;
        return $this->service->create($data);
    }

    function update(int $id){
        return $this->service->update($this->request->all(), $id);
    }

    function delete(int $id){
        return $this->service->delete($id);
    }

    function get(int $id){
        return $this->service->get($id);
    }
}
