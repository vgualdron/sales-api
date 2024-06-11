<?php
    namespace App\Services\Interfaces;
    
    interface AuthServiceInterface
    {
        function getActiveToken();
        function login(string $documentNumber, string $password);
        function logout();  
    }
?>