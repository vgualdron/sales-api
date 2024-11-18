<?php
    namespace App\Services\Interfaces;
    
    interface QuestonServiceInterface
    {
        function list(string $status);
        function create(array $question);
        function update(array $question, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>