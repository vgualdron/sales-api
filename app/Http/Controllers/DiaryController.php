<?php

namespace App\Http\Controllers;
use App\Models\Diary;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Implementations\DiaryServiceImplement;

class DiaryController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, DiaryServiceImplement $service) { 
        $this->request = $request;
        $this->service = $service;
    }

    function list(string $date, int $user, string $moment){
        return $this->service->list($date, $user, $moment);
    }
    
    function listDayByDay(string $date, int $user, string $moment){
        return $this->service->listDayByDay($date, $user, $moment);
    }

    function create(){
        return $this->service->create($this->request->all());
    }

    function update(int $id){
        return $this->service->update($this->request->all(), $id);
    }
    
    function updateStatus(int $id){
        return $this->service->updateStatus($this->request->all(), $id);
    }
 
    function delete(int $id){
        return $this->service->delete($id);
    }

    function get(int $id){
        return $this->service->get($id);
    }
}
