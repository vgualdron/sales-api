<?php

namespace App\Http\Controllers;
use App\Models\City;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\CityServiceImplement;

class CityController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, CityServiceImplement $service) {
        $this->request = $request;
        $this->service = $service;
    }

    function list(int $department){
        return $this->service->list($department);
    }
}
