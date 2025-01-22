<?php

namespace App\Http\Controllers;
use App\Models\Department;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\DepartmentServiceImplement;

class DepartmentController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, DepartmentServiceImplement $service) {
        $this->request = $request;
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }
}
