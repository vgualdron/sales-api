<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\UserServiceImplement;

class UserController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, UserServiceImplement $service) { 
            $this->request = $request;
            $this->service = $service;
    }

    function list(int $displayAll){
        return $this->service->list($displayAll);
    }

    function create(){
        return $this->service->create($this->request->all());
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

    function updateProfile(int $id){
        return $this->service->updateProfile($this->request->all(), $id);
    }
}
