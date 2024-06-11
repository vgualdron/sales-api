<?php
    namespace App\Services\Interfaces;
    
    interface MovementServiceInterface
    {
        function list();
        function getTickets(string $startDate, string $finalDate);
        function create(string $startDate, string $finalDate, string $tickets);
        function delete(int $id);
        function print(int $id);
    }
?>