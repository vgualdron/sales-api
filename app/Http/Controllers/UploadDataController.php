<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Novel;
use App\Models\Lending;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UploadDataController extends Controller
{
    public function createNew(Request $request)
    {
        try {
            $data = $request->all();
            $itemNovel = Novel::create([
                'document_number' => $request->document_number,
                'name' => $request->name,
                'phone' => $request->phone,
                'period' => $request->period,
                'quantity' => $request->quantity,
                'created_at' => date("Y-m-d H:i:s", (strtotime(date($request->created_at)) + 8640)),
                'lent_by' => $request->lent_by,
                'approved_by' => $request->approved_by,
                'address' => $request->address,
                'sector' => $request->sector,
                'district' => $request->district,
                'address_house' => $request->address,
                'address_house_sector' => $request->sector,
                'address_house_district' => $request->district,
                'observation' => $request->observation,
                'status' => $request->status,
                'attempts' => $request->attempts,
                'type_cv' => $request->type_cv,
                'site_visit' => $request->site_visit,
                'occupation' => $request->occupation,
                'type_work' => $request->type_work,
                'user_send' => $request->user_send,
                'type_house' => $request->type_house,
                'address_work' => $request->address_work,
                'address_work_sector' => $request->address_work_sector,
                'address_work_district' => $request->address_work_district,
                'guarantor_document_number' => $request->guarantor_document_number,
                'guarantor_name' => $request->guarantor_name,
                'guarantor_occupation' => $request->guarantor_occupation,
                'guarantor_phone' => $request->guarantor_phone,
                'guarantor_relationship' => $request->guarantor_relationship,
                'guarantor_district' => $request->guarantor_district,
                'guarantor_address' => $request->guarantor_address,
                'family_reference_name' => $request->family_reference_name,
                'family_reference_phone' => $request->family_reference_phone,
                'family_reference_relationship' => $request->family_reference_relationship,
                'family_reference_address' => $request->family_reference_address,
                'family_reference_district' => $request->family_reference_district,
                'family2_reference_name' => $request->family2_reference_name,
                'family2_reference_phone' => $request->family2_reference_phone,
                'family2_reference_relationship' => $request->family2_reference_relationship,
                'family2_reference_address' => $request->family2_reference_address,
                'family2_reference_district' => $request->family2_reference_district,
                'account_type' => $request->account_type,
                'account_number' => $request->account_number,
                'account_type_third' => $request->account_type_third,
                'account_number_third' => $request->account_number_third,
                'account_active' => $request->account_active,
                'has_letter' => $request->has_letter,
                'extra_reference' => $request->extra_reference,
            ]);

            $countDays = 1;
            $amountFees = 1;
            $date = $request->date_lending;
            $firstDate = date("Y-m-d H:i:s", (strtotime(date($date))));
           
            if ($request->period === 'diario') {
                $countDays = 21;
                $amountFees = 22;
            } else if ($request->period === 'semanal') {
                $countDays = 21;
                $amountFees = 3;
            } else if ($request->period === 'quincenal') {
                $countDays = 14;
                $amountFees = 1;
            }

            $endDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * $countDays) + 86399));
            $paymentDate = date("Y-m-d H:i:s", (strtotime(date($date)) + (86400 * 2) + 86300));

            $itemLending = Lending::create([
                'nameDebtor' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'firstDate' => $firstDate,
                'endDate' => $endDate,
                'amount' => $request->amount_lending,
                'amountFees' => $amountFees,
                'percentage' => 32,
                'period' => $request->period,
                'order' => 1,
                'status' => 'open',
                'listing_id' => $request->listing_id,
                'new_id' => $itemNovel->id,
                'type' => 'N',
                'created_at' => date("Y-m-d H:i:s", (strtotime(date($date)) + 8630)),
            ]);

            $itemPayment = Payment::create([
                'lending_id' => $itemLending->id,
                'date' => $paymentDate,
                'amount' => $request->amount_payment,
                'type' => 'nequi',
                'status' => 'verificado',
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
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function listNews(Request $request)
    {
        try {
            $items = Novel::where('status', 'migracion')->orderBy('created_at', 'desc')->get();
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

    public function uploadPayments(Request $request)
    {
        try {
            $data = $request->data;
            DB::transaction(function () use ($data) {
                foreach ($data as $item) {
                    Payment::create([
                        'reference' => $item['value'], // Cambia según los datos del array
                        'date' => date("Y-m-d H:i:s"),
                        'observation' => 'insert manual',
                        'type' => 'old',
                        'status' => 'verificado',
                    ]);
                }
            });
            
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
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }
}