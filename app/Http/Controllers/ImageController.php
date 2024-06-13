<?php

namespace App\Http\Controllers;

// use App\Models\Image;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function create(Request $request)
    {
        try {
            // $userSesion = $request->user();
            // $idUserSesion = $userSesion->id;
            // $productId = $request->product_id;
            // Obtener los datos de la imagen
            $image_avatar_b64 = $request->image;
            $img = $this->getB64Image($image_avatar_b64);
            // Obtener la extensión de la Imagen
            $img_extension = $this->getB64Extension($image_avatar_b64);
            // Crear un nombre aleatorio para la imagen
            $img_name = strtotime("now") . '.' . $img_extension;
            // echo $image_name;
            // Usando el Storage guardar en el disco creado anteriormente y pasandole a 
            // la función "put" el nombre de la imagen y los datos de la imagen como 
            // segundo parametro

           //  Storage::disk('public')->put($img_name, $img);
            
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
                'data' => [],
                'message'=>$e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'data' => 'OK',
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

    
    protected function getB64Image($base64_image)
    {  
         // Obtener el String base-64 de los datos         
         $image_service_str = substr($base64_image, strpos($base64_image, ",")+1);
         // Decodificar ese string y devolver los datos de la imagen        
         $image = base64_decode($image_service_str);   
         // Retornamos el string decodificado
         return $image; 
    }
    
    protected function getB64Extension($base64_image, $full=null){  
        // Obtener mediante una expresión regular la extensión imagen y guardarla
        // en la variable "img_extension"        
        preg_match("/^data:image\/(.*);base64/i",$base64_image, $img_extension);   
        // Dependiendo si se pide la extensión completa o no retornar el arreglo con
        // los datos de la extensión en la posición 0 - 1
        return ($full) ?  $img_extension[0] : $img_extension[1];  
    }
}