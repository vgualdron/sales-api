<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\CapitallistingServiceImplement;

class CapitallistingController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, CapitallistingServiceImplement $service) {
            $this->request = $request;
            $this->service = $service;
    }

    function create() {
        return $this->service->create($this->request->all());
    }
}
