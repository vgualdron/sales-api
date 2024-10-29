<?php

namespace App\Http\Controllers;
use App\Models\Expense;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\ExpenseServiceImplement;

class ExpenseController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, ExpenseServiceImplement $service) { 
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
