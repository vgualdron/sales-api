<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\ReportServiceImplement;

class ReportController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, ReportServiceImplement $service) { 
        $this->request = $request;
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }

    function execute(int $id){
        return $this->service->execute($id);
    }
}
