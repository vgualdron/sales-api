<?php
    namespace App\Services\Interfaces;
    
    interface AdjustmentServiceInterface
    {
        function list();
        function create(array $adjustment);
        function update(array $adjustment, int $id);
        function delete(int $id); 
        function get(int $id);
        function createFromProccess(array $data);
    }
?>