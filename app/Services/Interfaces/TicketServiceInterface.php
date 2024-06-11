<?php
    namespace App\Services\Interfaces;
    
    interface TicketServiceInterface
    {
        function list();
        function create(array $ticket);
        function update(array $ticket, int $id);
        function delete(int $id); 
        function get(int $id);
    }
?>