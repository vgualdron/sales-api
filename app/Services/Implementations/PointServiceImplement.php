<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\PointServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Point;
    use App\Validator\{UserValidator, ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;

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

        function create(array $point){
            try {
                DB::transaction(function () use ($point) {
                    $sql = $this->point::create([
                        'amount' => $point['amount'],
                        'status' => $point['status'],
                        'description' => $point['description'],
                        'observation' => $point['observation'],
                        'user_id' => $point['user_id'],
                    ]);

                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Registrado con éxito',
                            'detail' => null
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
