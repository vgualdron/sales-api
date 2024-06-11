<?php
    namespace App\Services\Interfaces;
    
    interface BatterieServiceInterface
    {
        function list();
        function create(array $batterie);
        function update(array $batterie, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>