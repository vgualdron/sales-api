<?php
    namespace App\Services\Interfaces;

    interface PointServiceInterface
    {
        function list(string $status);
        function listByUserSession(string $status, int $id);
        function create(array $point);
        function update(array $point, int $id);
        function delete(int $id);
        function get(int $id);
    }
?>
