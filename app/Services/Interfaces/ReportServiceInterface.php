<?php
    namespace App\Services\Interfaces;
    
    interface ReportServiceInterface
    {
        function list();
        function execute(int $id);
    }
?>