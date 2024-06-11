<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\YardServiceImplement;

class YardController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, YardServiceImplement $service) { 
            $this->request = $request;
            $this->service = $service;
    }

    function list(string $yard, string $displayAll){
        return $this->service->list($yard, $displayAll);
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
