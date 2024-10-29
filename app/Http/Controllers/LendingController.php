<?php

namespace App\Http\Controllers;

use App\Models\Lending;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\File;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class LendingController extends Controller
{
    public function index(Request $request, $idList)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::where('listing_id', '=', $idList)
                                ->with('payments')
                                // ->with('interests')
                                ->where('status', '=', 'open')
                                ->orderBy('order', 'asc')->get();
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed',
        ], JsonResponse::HTTP_OK);
    }
    
    public function getLendingsWithPaymentsCurrentDate(Request $request, $idList)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::select('lendings.*')
                                ->leftjoin('payments', 'lendings.id', 'payments.lending_id')
                                // ->leftjoin('files', 'lendings.id', 'interests.lending_id')
                                ->with('payments')
                                // ->with('file')
                                ->where('listing_id', '=', $idList)
                                // ->where('payments.date', '<=', date("Y-m-d h:i:s"))
                                // ->where('payments.amount', '=', NULL)
                                ->where('lendings.status', '=', 'open')
                                ->distinct()
                                ->orderBy('lendings.order', 'asc')->get();
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed',
        ], JsonResponse::HTTP_OK);
    }
    
    public function getLendingsFromListCurrentDate(Request $request, $idList)
    {
        $date = date("Y-m-d");
        $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
        $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + 86399));
        
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::where('listing_id', '=', $idList)
                                ->whereBetween('created_at', [$firstDate, $endDate])
                                ->distinct()
                                ->orderBy('lendings.order', 'asc')->get();
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed',
        ], JsonResponse::HTTP_OK);
    }
  
    public function show(Request $request, $id)
    {
        try {
            $items = Lending::where('id', '=', $id)
                ->with('payments')
                // ->with('interests')
                ->first();
            
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $idUserSesion = $request->user()->id;
            $period = $request->period;
            $countDays = 1;
            $amountFees = 1;
            
            $date = date("Y-m-d");
            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
           
            if ($period === 'diario') {
                $countDays = 21;
                $amountFees = 22;
            } else if ($period === 'semanal') {
                $countDays = 21;
                $amountFees = 3;
            } else if ($period === 'quincenal') {
                $countDays = 14;
                $amountFees = 1;
            }

            $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * $countDays) + 86399));

            $idList = 1;
            $idUserExpense = 1;

            $result = DB::select("SELECT
                                lis.id as id,
                                lis.name as name,
                                lis.user_id_collector as user_id,
                                COALESCE(SUM(len.amount), 0) AS capital
                                FROM listings lis
                                LEFT JOIN lendings as len ON lis.id = len.listing_id AND len.status = 'open'
                                GROUP BY lis.id
                                ORDER BY COALESCE(SUM(len.amount), 0) ASC;");

            if (!empty($result)) {
                $firstRow = $result[0];
                $idList = $firstRow->id;
                $idUserExpense = $firstRow->user_id;
            }

            $statusLending = Lending::create([
                'nameDebtor' => $request->nameDebtor,
                'address' => $request->address,
                'phone' => $request->phone,
                'firstDate' => $firstDate,
                'endDate' => $endDate,
                'amount' => $request->amount,
                'amountFees' => $amountFees,
                'percentage' => $request->percentage,
                'period' => $period,
                'order' => $request->order,
                'status' => $request->status,
                'listing_id' => $idList,
                'new_id' => $request->new_id,
                'type' => $request->type,
            ]);

            $itemFile = File::where('name', 'FOTO_VOUCHER')
            ->where('model_id', $request->new_id)
            ->where('model_name', 'news')
            ->first();
            
            $statusExpense = Expense::create([
                'date' => $firstDate,
                'amount' => $request->amount,
                'status' => 'creado',
                'description' => 'Egreso creado automaticamente cuando se aprueba el voucher de consignación del nuevo',
                'item_id' => 8, // id del item de egreso para NUEVOS
                'user_id' => $idUserExpense,
                'file_id' => $itemFile->id,
                'registered_by' => $idUserSesion,
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'Creado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        try {
            $items = Lending::find($id)
                        ->update($request->all());
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'Modificado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $items = Lending::destroy($id);
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'Eliminado con éxito.',
                    'detail' => null,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }
    
    public function updateOrderRows(Request $request)
    {
        try {
            $rows = $request->all();
            
            $items = [];
            $index = 1;
            foreach($rows['rows'] as $row){
                $item = Lending::find($row['id'])->update([
                    'order' => $index
                ]);
                
                $items[] = $item;
                $index++;
            }
         
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function renovate(Request $request, $id)
    {
        try {
            $newItem = [
                'status' => $request->status,
            ];

            $item = Lending::find($id);

            $item->update($newItem);

            $period = $item->period;
            $countDays = 1;
            $amountFees = 1;
            
            $date = $request->date;
            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
            $currentDate = date("Y-m-d H:i:s");
           
            if ($period === 'diario') {
                $countDays = 21;
                $amountFees = 22;
            } else if ($period === 'semanal') {
                $countDays = 21;
                $amountFees = 3;
            } else if ($period === 'quincenal') {
                $countDays = 14;
                $amountFees = 1;
            }

            $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * $countDays) + 86399));

            $idList = $item->listing_id;
            $amount = $request->amount;
            $amountNew = $request->amountNew;

            $idUserExpense = 1;

            $result = DB::select("SELECT
                                lis.id as id,
                                lis.name as name,
                                lis.user_id_collector as user_id,
                                COALESCE(SUM(len.amount), 0) AS capital
                                FROM listings lis
                                LEFT JOIN lendings as len ON lis.id = len.listing_id AND len.status = 'open'
                                GROUP BY lis.id
                                ORDER BY COALESCE(SUM(len.amount), 0) ASC;");

            if (!empty($result)) {
                $firstRow = $result[0];
                $idList = $firstRow->id;
                $idUserExpense = $firstRow->user_id;
            }
            
            if ($amountNew > 0) {
                $amount = $amount + $amountNew;

                $statusExpense = Expense::create([
                    'date' => $currentDate,
                    'amount' => $amount,
                    'status' => 'creado',
                    'description' => 'Egreso creado automaticamente cuando se renueva un credito por encima del valor que tenia prestado',
                    'item_id' => 1, // id del item de egreso para RENOVACIONES DE NEQUI
                    'user_id' => $idUserExpense,
                    'registered_by' => $idUserSesion,
                ]);
            }

            $item = Lending::create([
                'nameDebtor' => $item->nameDebtor,
                'address' => $item->address,
                'phone' => $item->phone,
                'firstDate' => $firstDate,
                'endDate' => $endDate,
                'amount' => $amount,
                'amountFees' => $amountFees,
                'percentage' => $item->percentage,
                'period' => $period,
                'order' => $item->order,
                'status' => 'open',
                'listing_id' => $idList,
                'new_id' => $item->new_id,
                'type' => 'normal',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'Modificado con éxito.',
                    'detail' => $item,
                ]
            ]
        ], JsonResponse::HTTP_OK);
    }

    public function history(Request $request, $idNew)
    {
        try {
            $idUserSesion = $request->user()->id;
            $items = Lending::where('new_id', '=', $idNew)
                                ->with('payments')
                                ->orderBy('order', 'asc')->get();
        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed',
        ], JsonResponse::HTTP_OK);
    }

}