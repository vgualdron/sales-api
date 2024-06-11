<?php
    namespace App\Services\Interfaces;
    
    interface YardServiceInterface
    {
        function list(string $yard, int $displayAll);
        function create(array $zone);
        function update(array $zone, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>