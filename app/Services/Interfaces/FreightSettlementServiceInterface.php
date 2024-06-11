<?php
    namespace App\Services\Interfaces;
    
    interface FreightSettlementServiceInterface
    {
        function list();
        function getTickets(string $startDate, string $finalDate, int $conveyorCompany);
        function settle(array $data);
        function print(int $id);
        function get(int $id);
        function addInformation(array $data, int $id);
        function validateMovements(int $id);
        function update(array $data, int $id);
        function getSettledTickets(int $id);
        function delete(int $id);
    }
?>