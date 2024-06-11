<?php
    namespace App\Services\Interfaces;
    
    interface RateServiceInterface
    {
        function list();
        function create(array $rate);
        function update(array $rate, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>