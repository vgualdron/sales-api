<?php

namespace App\Http\Controllers;
use App\Models\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\ShopServiceImplement;

class ShopController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, ShopServiceImplement $service) {
        $this->request = $request;
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }
}
