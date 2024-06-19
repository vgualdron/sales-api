<?php

namespace App\Http\Controllers;

// use App\Models\Image;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function create(Request $request)
    {
        $url = "";
        try {
            // $userSesion = $request->user();
            // $idUserSesion = $userSesion->id;
            // $productId = $request->product_id;
            // Obtener los datos de la imagen
            $file = $request->file;
            $extension = $request->extension;
            $f = $this->getB64Image($file);
          
            // Crear un nombre aleatorio para la imagen
            $name = strtotime("now") . '.' . $extension;
            $url = "https://micomercio.com.co/api-prestamos/storage/app/public/images/".$name;
            // echo $image_name;
            // Usando el Storage guardar en el disco creado anteriormente y pasandole a 
            // la función "put" el nombre de la imagen y los datos de la imagen como 
            // segundo parametro

            Storage::disk('public')->put($name, $f);
            
            /* $imgBrand = Image::make(public_path('images/products/'.$image_name));
            $img->insert(public_path('images/brand/logo-rectangle.png'), 'bottom-right', 10, 10);
            $img->save(public_path('images/main-new.png')); */
            /* $item = Image::create([
                'name' => $img_name,
                'product_id' => $productId,
                'order' => $request->order,
            ]); */

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

    protected function getB64Image($base64_image)
    {  
        // Decodificar ese string y devolver los datos de la imagen        
        $image = base64_decode($base64_image);   
        // Retornamos el string decodificado
        return $image; 
    }
}