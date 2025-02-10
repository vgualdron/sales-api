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
}
