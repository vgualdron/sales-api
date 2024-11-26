<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\QuestionServiceImplement;

class QuestionController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, QuestionServiceImplement $service) { 
            $this->request = $request;
            $this->service = $service;
    }

    function list(string $status, string $type){
        return $this->service->list($status, $type);
    }

    function create(){
        $item = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $item["registered_by"] = $idUserSesion;
        return $this->service->create($item);
    }

    function update(int $id){
        $item = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $item["answered_by"] = $idUserSesion;
        return $this->service->update($item, $id);
    }

    function delete(int $id){
        return $this->service->delete($id);
    }

    function get(int $id){
        return $this->service->get($id);
    }

    function getStatus(){
        $item = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $item["registered_by"] = $idUserSesion;
        return $this->service->getStatus($item);
    }
}
