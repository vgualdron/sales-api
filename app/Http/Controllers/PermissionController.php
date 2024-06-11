<?php

namespace App\Http\Controllers;

use App\Services\Implementations\PermissionServiceImplement;

class PermissionController extends Controller
{
    private $service;

    public function __construct(PermissionServiceImplement $service) {
            $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }
}
