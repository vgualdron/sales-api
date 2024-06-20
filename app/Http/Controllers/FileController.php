<?php

namespace App\Http\Controllers;

use App\Models\File;
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
            // $userSesion = $request->user();
            // $idUserSesion = $userSesion->id;
            // $productId = $request->product_id;
            // Obtener los datos de la imagen
            $name = $request->name;
            $modelName = $request->model_name;
            $modelId = $request->model_id;
            $type = $request->type;
            $file = $request->file;
            $extension = $request->extension;
            $storage = $request->storage;

            $f = base64_decode($file);
          
            // Crear un nombre aleatorio para la imagen
            $nameComplete = $name . '.' . $extension;

            Storage::disk($storage)->makeDirectory($modelId);
            $url = Storage::disk($storage)->putFile("$modelId/$nameComplete", $f);
         
            $item = File::create([
                'name' => $name,
                'model_name' => $modelName,
                'model_id' => $modelId,
                'type' => $type,
                'extension' => $extension,
                'url' => $url,
            ]);

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
                    'text' => 'Succeed',
                    'detail' => $url
                ]
            ]
        ], Response::HTTP_OK);

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