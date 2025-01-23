<?php

namespace App\Http\Controllers;
use App\Models\Company;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\CompanyServiceImplement;

class CompanyController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, CompanyServiceImplement $service) {
        $this->request = $request;
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }
}
