<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\ThirdServiceImplement;

class ThirdController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, ThirdServiceImplement $service) { 
            $this->request = $request;
            $this->service = $service;
    }

    function list(int $displayAll, string $type, string $third, string $origin, string $startDate, string $finalDate){
        return $this->service->list($displayAll, $type, $third, $origin, $startDate, $finalDate);
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

    function createInBatch(){
        return $this->service->createInBatch($this->request->all());
    }
}
