<?php
    namespace App\Services\Interfaces;

    interface AuthServiceInterface
    {
        function getActiveToken();
        function login(string $email, string $password);
        function logout();
    }
?>
