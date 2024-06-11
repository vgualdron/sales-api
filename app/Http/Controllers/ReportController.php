<?php

namespace App\Http\Controllers;

use App\Services\Implementations\ReportServiceImplement;

class ReportController extends Controller
{
    private $service;

    public function __construct(ReportServiceImplement $service) { 
        $this->service = $service;
    }

    function movements(string $movement, string $startDate, string $finalDate, int $originYard, int $destinyYard, int $material){
        return $this->service->movements($movement, $startDate, $finalDate, $originYard, $destinyYard, $material);
    }
    
    function yardStock(string $date){
        return $this->service->yardStock($date);
    }
    
    function completeTransfers(string $startDate, string $finalDate, int $originYard, int $destinyYard){
        return $this->service->completeTransfers($startDate, $finalDate, $originYard, $destinyYard);
    }
    
    function uncompleteTransfers(string $startDate, string $finalDate, int $originYard, int $destinyYard){
        return $this->service->uncompleteTransfers($startDate, $finalDate, $originYard, $destinyYard);
    }
    
    function unbilledPurchases(string $startDate, string $finalDate, int $supplier, int $material){
        return $this->service->unbilledPurchases($startDate, $finalDate, $supplier, $material);
    }
    
    function unbilledSales(string $startDate, string $finalDate, int $customer, int $material){
        return $this->service->unbilledSales($startDate, $finalDate, $customer, $material);
    }
    
    function unbilledFreights(string $startDate, string $finalDate, int $conveyorCompany, int $material){
        return $this->service->unbilledFreights($startDate, $finalDate, $conveyorCompany, $material);
    }
}
