<?php
    namespace App\Services\Interfaces;
    
    interface DistrictServiceInterface
    {
        function list();
        function create(array $district);
        function update(array $district, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>