<?php

namespace App\Http\Controllers;
use App\Models\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\CollectionServiceImplement;

class CollectionController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, CollectionServiceImplement $service) {
        $this->request = $request;
        $this->service = $service;
    }

    function list(String $document){
        return $this->service->list($document);
    }
}
