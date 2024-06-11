<?php
    namespace App\Services\Interfaces;
    
    interface ZoneServiceInterface
    {
        function list();
        function create(array $zone);
        function update(array $zone, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>