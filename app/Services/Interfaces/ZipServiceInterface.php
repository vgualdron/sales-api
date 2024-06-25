<?php
    namespace App\Services\Interfaces;
    
    interface ZipServiceInterface
    {
        function list();
        function create(array $zip);
    }
?>