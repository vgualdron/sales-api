<?php
    namespace App\Services\Interfaces;
    
    interface UserServiceInterface
    {
        function list(int $displayAll);
        function get(int $id);
        function create(array $user);
        function update(array $user, int $id);
        function delete(int $user);
        function updateProfile(array $user, int $id);
    }
?>