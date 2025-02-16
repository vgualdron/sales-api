<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function create(Request $request)
    {
        $url = "";
        try {
            $userSesion = $request->user();
            $idUserSesion = $userSesion->id;
            $name = $request->name;
            $modelName = $request->modelName;
            $modelId = $request->modelId;
            $type = $request->type;
            $file = $request->file;
            $extension = $request->extension;
            $storage = $request->storage;
            $state = $request->status;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $maintainFile = $request->maintain;
            $item = null;
            $f = base64_decode($file);

            // Crear un nombre aleatorio para la imagen
            $time = strtotime("now");
            $nameComplete = $name."-".$time.".".$extension;
            $path = "$modelId/$nameComplete";
            $url = "/storage/app/public/$storage/$path";

            Storage::disk($storage)->makeDirectory($modelId);
            $status = Storage::disk($storage)->put($path, $f);

            if (!$maintainFile) {
                File::where('name', $name)
                    ->where('model_id', $modelId)
                    ->where('model_name', $modelName)
                    ->delete();
            }

            $item = File::create([
                'name' => $name,
                'model_name' => $modelName,
                'model_id' => $modelId,
                'type' => $type,
                'extension' => $extension,
                'url' => $url,
                'registered_by' => $idUserSesion,
                'registered_date' => date('Y-m-d H:i:s'),
                'status' => $state,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            if ($name == 'FOTO_PROFILE') {
                $sql = User::find($idUserSesion)->first();
                if(!empty($sql)) {
                    $sql->updatePhoto = true;
                    $sql->save();
                }
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => [
                    [
                        'text' => 'Se ha presentado un error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => [
                [
                    'text' => 'OK',
                    'detail' => 'Guardado con éxito.'
                ]
            ],
            'data' => $item,
        ], Response::HTTP_OK);

    }

    public function get(Request $request)
    {
        try {
            $userSesion = $request->user();
            $idUserSesion = $userSesion->id;
            $name = $request->name;
            $modelName = $request->modelName;
            $modelId = $request->modelId;
            $item = File::where('name', $name)
                ->where('model_id', $modelId)
                ->where('model_name', $modelName)
                ->first();

        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $item,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    function update(Request $request, int $id) {
        try {
            $item = File::find($id)->update($request->all());
        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $item,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }

    public function delete(Request $request, $id)
    {
        try {
            $item = Image::find($id);
            Storage::disk('products')->delete($item->name);
            $items = Image::destroy($id);
        } catch (Exception $e) {
            return response()->json([
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => $items,
            'message' => 'Succeed'
        ], JsonResponse::HTTP_OK);
    }
}
