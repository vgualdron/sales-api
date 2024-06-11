<?php
    namespace App\Services\Interfaces;
    
    interface ReportServiceInterface
    {
        function movements(string $movement, string $startDate, string $finalDate, int $originYard, int $destinyYard, int $material);
        function yardStock(string $date);
        function completeTransfers(string $startDate, string $finalDate, int $originYard, int $destinyYard);
        function uncompleteTransfers(string $startDate, string $finalDate, int $originYard, int $destinyYard);
        function unbilledPurchases(string $startDate, string $finalDate, int $supplier, int $material);
        function unbilledSales(string $startDate, string $finalDate, int $customer, int $material);
        function unbilledFreights(string $startDate, string $finalDate, int $conveyorCompany, int $material);
    }
?>