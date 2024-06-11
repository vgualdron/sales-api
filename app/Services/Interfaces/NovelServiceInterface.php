<?php
    namespace App\Services\Interfaces;
    
    interface NovelServiceInterface
    {
        function list(string $status);
        function create(array $novel);
        function update(array $novel, int $id);
        function updateStatus(array $novel, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>