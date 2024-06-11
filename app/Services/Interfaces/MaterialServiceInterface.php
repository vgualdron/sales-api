<?php
    namespace App\Services\Interfaces;
    
    interface MaterialServiceInterface
    {
        function list(int $displayAll, string $material);
        function create(array $material);
        function update(array $material, int $id);
        function delete(int $id); 
        function get(int $id);
        function getMaterialsByYard(int $yard);
    }
?>