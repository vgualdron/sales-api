<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\FreightSettlementServiceImplement;

class FreightSettlementController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, FreightSettlementServiceImplement $service) { 
        $this->request = $request;
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }

    function getTickets(string $startDate, string $finalDate, int $conveyorCompany){
        return $this->service->getTickets($startDate, $finalDate, $conveyorCompany);
    }

    function settle() {
        return $this->service->settle($this->request->all());
    }

    function print(int $id) {
        return $this->service->print($id);
    }

    function get(int $id) {
        return $this->service->get($id);
    }

    function update(int $id) {
        return $this->service->update($this->request->all(), $id);
    }

    function addInformation(int $id){
        return $this->service->addInformation($this->request->all(), $id);
    }

    
    function validateMovements(int $id) {
        return $this->service->validateMovements($id);
    }

    function getSettledTickets(int $id) {
        return $this->service->getSettledTickets($id);
    }

    function delete(int $id) {
        return $this->service->delete($id);
    }
}
