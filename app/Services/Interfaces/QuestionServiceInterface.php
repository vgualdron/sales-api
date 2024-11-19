<?php
    namespace App\Services\Interfaces;
    
    interface QuestionServiceInterface
    {
        function list(string $status);
        function create(array $question);
        function update(array $question, int $id);
        function delete(int $id); 
        function get(int $id);
        function getStatus(array $question);
    }
?>