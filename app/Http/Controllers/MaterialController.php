<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\MaterialServiceImplement;

class MaterialController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, MaterialServiceImplement $service) { 
        $this->request = $request;
        $this->service = $service;
    }

    function list(int $displayAll, string $material){
        return $this->service->list($displayAll, $material);
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

    function getMaterialsByYard(int $yard){
        return $this->service->getMaterialsByYard($yard);
    }
}
