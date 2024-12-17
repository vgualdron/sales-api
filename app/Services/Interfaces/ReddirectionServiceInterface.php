<?php
    namespace App\Services\Interfaces;
    
    interface ReddirectionServiceInterface
    {
        function create(array $reddirection);
        function getCurrentByUser(int $user);
    }
?>