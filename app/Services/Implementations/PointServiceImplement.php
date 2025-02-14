<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\PointServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Point;
    use App\Models\File;
    use App\Validator\{UserValidator, ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;

    class PointServiceImplement implements PointServiceInterface {

        use Commons;

        private $point;
        private $validator;
        private $profileValidator;

        function __construct(ProfileValidator $profileValidator){
            $this->point = new Point;
            $this->profileValidator = $profileValidator;
        }

        function list(string $status) {
            try {
                $explodeStatus = explode(',', $status);
                $sql = $this->point->from('points as p')
                            ->select(
                                'p.*',
                                'u.*',
                            )
                            ->join('users as u', 'u.id', 'p.user_id')
                            ->when($status !== 'all', function ($q) use ($explodeStatus) {
                                return $q->whereIn('n.status', $explodeStatus);
                            })
                            ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay usuarios para mostrar',
                                'detail' => 'Aun no ha registrado ningun registro'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los usuarios',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function listByUserSession(string $status, int $id) {
            try {
                $explodeStatus = explode(',', $status);
                $sql = $this->point->from('points as p')
                            ->select(
                                'p.*',
                            )
                            ->join('users as u', 'u.id', 'p.user_id')
                            ->where('p.user_id', $id)
                            ->when($status !== 'all', function ($q) use ($explodeStatus) {
                                return $q->whereIn('p.status', $explodeStatus);
                            })
                            ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay puntos para mostrar',
                                'detail' => 'Aun no ha registrado ningun registro'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los puntos',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function create(array $point){
            try {
                DB::transaction(function () use ($point) {
                    $sql = $this->point::create($point);

                    $idUserSesion = $point["registered_by"];
                    $name = 'FOTO_PUNTOS';
                    $modelName = 'points';
                    $modelId = $sql->id;
                    $type = 'image';
                    $file = base64_decode($point["photo"]);
                    $extension = 'jpg';
                    $storage = 'points';
                    $state = 'aprobado';
                    $latitude = null;
                    $longitude = null;
                    $item = null;

                    // Crear un nombre aleatorio para la imagen
                    $time = strtotime("now");
                    $nameComplete = $name."-".$time.".".$extension;
                    $path = "$modelId/$nameComplete";
                    $url = "/storage/app/public/$storage/$path";

                    Storage::disk($storage)->makeDirectory($modelId);
                    $status = Storage::disk($storage)->put($path, $f);

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

                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Registrado con éxito',
                            'detail' => $item
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $point, int $id){
            try {
                $sql = $this->point::find($id);
                if(!empty($sql)) {
                    DB::transaction(function () use ($sql, $point) {
                        $sql->amount = $point['amount'];
                        $sql->status = $point['status'];
                        $sql->observation = $point['observation'];
                        $sql->description = $point['description'];
                        $sql->user_id = $point['user_id'];
                        $sql->save();
                    });
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Actualizado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar',
                                'detail' => 'El registro no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el usuario',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){
            try {
                $sql = $this->point::find($id);
                if(!empty($sql)) {
                    $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Registro eliminado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar',
                                'detail' => 'El registro no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al eliminar el registro',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function get(int $id){
            try {
                $sql = $this->point::select(
                    'amount',
                    'status',
                    'observation',
                    'description',
                    'user_id',
                )
                ->where('id', $id)
                ->first();

                return response()->json([
                    'data' => $sql
                ], Response::HTTP_OK);

            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar el usuario',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>
