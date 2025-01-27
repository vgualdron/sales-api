<?php

namespace App\Http\Controllers;
use App\Models\CreditLine;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\CreditLineServiceImplement;

class CreditLineController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, CreditLineServiceImplement $service) {
        $this->request = $request;
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }
}
