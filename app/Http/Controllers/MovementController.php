<?php

namespace App\Http\Controllers;

use App\Services\Implementations\MovementServiceImplement;

class MovementController extends Controller
{
    private $service;

    public function __construct(MovementServiceImplement $service) { 
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }

    function getTickets(string $startDate, string $finalDate){
        return $this->service->getTickets($startDate, $finalDate);
    }

    function create(string $startDate, string $finalDate, string $tickets){
        return $this->service->create($startDate, $finalDate, $tickets);
    }

    function delete(int $id){
        return $this->service->delete($id);
    }


    function print(int $id){
        return $this->service->print($id);
    }
}
