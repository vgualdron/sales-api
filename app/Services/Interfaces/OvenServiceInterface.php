<?php
    namespace App\Services\Interfaces;
    
    interface OvenServiceInterface
    {
        function list();
        function create(array $batterie);
        function update(array $batterie, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>