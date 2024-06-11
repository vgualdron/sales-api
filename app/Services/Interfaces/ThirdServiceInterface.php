<?php
    namespace App\Services\Interfaces;
    
    interface ThirdServiceInterface
    {
        function list(int $displayAll, string $type, string $third, string $origin, string $startDate, string $finalDate);
        function create(array $third);
        function update(array $third, int $id);
        function delete(int $id); 
        function get(int $id);
        function createInBatch(array $data);
    }
?>