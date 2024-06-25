<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Implementations\ZipServiceImplement;

class ZipController extends Controller
{
    private $service;
    private $request;

    public function __construct(Request $request, ZipServiceImplement $service) { 
        $this->request = $request;
        $this->service = $service;
    }

    function list(){
        return $this->service->list();
    }

    function createReal() {
        $zone = $this->request->all();
        $userSesion = $this->request->user();
        $idUserSesion = $userSesion->id;
        $zone["registered_by"] = $idUserSesion;
        return $this->service->create($zone);
    }

    public function create()
    {
        // Directorio que quieres escanear
        $directory = storage_path('app/public/news');

        // Nombre del archivo ZIP
        $zipFileName = "app/public/news/files.zip";
        $zipFilePath = storage_path($zipFileName);

        // Crear una instancia de ZipArchive
        $zip = new ZipArchive();

        // Abrir el archivo ZIP para escribir
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return response()->json(['error' => 'No se puede crear el archivo ZIP'], 500);
        }

        // Escanear el directorio y agregar archivos al archivo ZIP
        $this->addFilesToZip($zip, $directory);

        // Cerrar el archivo ZIP
        $zip->close();

        // Configurar encabezados para la descarga del archivo ZIP
        return response()->download($zipFilePath)->deleteFileAfterSend(false);
    }

    private function addFilesToZip($zip, $directory, $baseDir = '')
    {
        $files = File::allFiles($directory);
        foreach ($files as $file) {
            $relativePath = $baseDir . $file->getRelativePathname();
            $zip->addFile($file->getRealPath(), $relativePath);
        }

        $directories = File::directories($directory);
        foreach ($directories as $dir) {
            $this->addFilesToZip($zip, $dir, $baseDir . basename($dir) . '/');
        }
    }
}
