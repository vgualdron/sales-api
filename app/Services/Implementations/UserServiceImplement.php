<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\UserServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\User;
    use App\Validator\{UserValidator, ProfileValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;
    
    class UserServiceImplement implements UserServiceInterface {

        use Commons;

        private $user;
        private $validator;
        private $profileValidator;

        function __construct(UserValidator $validator, ProfileValidator $profileValidator){
            $this->user = new User;
            $this->validator = $validator;
            $this->profileValidator = $profileValidator;
        }    

        function list(int $displayAll){
            try {
                $sql = $this->user->from('users as u')
                            ->select(
                                'u.id',
                                'u.document_number as documentNumber',
                                'u.name as name',
                                'u.phone as phone',
                                'u.push_token as pushToken',
                                DB::Raw('IF(u.active = 1, "ACTIVO", "NO ACTIVO") as status'),
                                DB::Raw('IF(u.yard IS NOT NULL, y.name, "Sin sector asignado") as yard'),
                                DB::Raw('IF(y.zone IS NOT NULL, z.name, "Sin ciudad asignada") as zone')
                            )
                            ->leftJoin('yards as y', 'u.yard', 'y.id')
                            ->leftJoin('zones as z', 'y.zone', 'z.id')
                            ->when($displayAll === 0, function ($q) {
                                return $q->where('u.active', 1);
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
        
        function listByRoleName(int $displayAll, string $name, int $city){
            try {
                $sql = $this->user->from('users as u')
                            ->select(
                                'u.id',
                                'u.document_number as documentNumber',
                                'u.name as name',
                                'u.phone as phone',
                                'r.name as role',
                                'u.push_token as pushToken',
                                DB::Raw('IF(u.active = 1, "ACTIVO", "NO ACTIVO") as status'),
                                DB::Raw('IF(u.yard IS NOT NULL, y.name, "Sin sector asignado") as yard'),
                                DB::Raw('IF(y.zone IS NOT NULL, z.name, "Sin ciudad asignada") as zone')
                            )
                            ->leftJoin('yards as y', 'u.yard', 'y.id')
                            ->leftJoin('zones as z', 'y.zone', 'z.id')
                            ->join('model_has_roles as mhr', 'u.id', 'mhr.model_id')
                            ->join('roles as r', 'mhr.role_id', 'r.id')
                            ->where('r.name', 'LIKE', '%' . $name . '%')
                            ->where('u.active', $displayAll)
                            ->when($city > 0, function ($q) use ($city) {
                                return $q->where('z.id', $city);
                            })
                            ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'data' => []
                    ], Response::HTTP_OK);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function create(array $user){
            try {
                $validation = $this->validate($this->validator, $user, null, 'registrar', 'usuario', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                DB::transaction(function () use ($user) {
                    $sql = $this->user::create([
                        'document_number' => $user['documentNumber'],
                        'name' => $user['name'],
                        'phone' => $user['phone'],
                        'active' => $user['active'],
                        'password' => empty($user['password']) ? Hash::make($user['documentNumber']) : Hash::make($user['password']),
                        'yard' => $user['yard'],
                        'change_yard' => $user['changeYard']
                    ]);
    
                    $sql->assignRole($user['roles']);
                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Usuario registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar el usuario',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $user, int $id){
            try {
                $validation = $this->validate($this->validator, $user, $id, 'actualizar', 'usuario', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->user::find($id);
                if(!empty($sql)) {
                    DB::transaction(function () use ($sql, $user) {
                        $sql->document_number = $user['documentNumber'];
                        $sql->name = $user['name'];
                        $sql->phone = $user['phone'];
                        $sql->yard = $user['yard'];
                        $sql->active = $user['active'];
                        $sql->password = empty($user['password']) ? $sql->password : Hash::make($user['password']);
                        $sql->change_yard = $user['changeYard'];
                        $sql->save();
                        $sql->roles()->detach();
                        $sql->assignRole($user['roles']);
                    });
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Usuario actualizado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar el usuario',
                                'detail' => 'El usuario no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el usuario',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->user::find($id);
                if(!empty($sql)) {
                    $adminUser = $sql->roles->where('pivot.role_id', 1)->count();
                    if ($adminUser === 0) {
                        $sql->delete();
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Usuario eliminado con éxito',
                                    'detail' => null
                                ]
                            ]
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Acción no permitida',
                                    'detail' => 'No puede eliminar usuarios relacionados al rol administrador'
                                ]
                            ]
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el usuario',
                                'detail' => 'El usuario no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el usuario',
                                'detail' => 'Si este problema persiste, contacte con un administrador'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar el usuario',
                                'detail' => 'El usuaio se encuentra asociado a otro registro'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->user::select(
                    'id',
                    'document_number as documentNumber',
                    'name',
                    'yard',
                    'phone',
                    'active',
                    'editable',
                    'change_yard as changeYard'
                )
                    ->where('id', $id)
                    ->first();
                if(!empty($sql)) {
                    $roles = $sql->roles->pluck('id');
                    unset($sql->roles);
                    $sql->roles = $roles;
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'El usuario no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar el usuario',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function updateProfile(array $user, int $id){
            try {
                $validation = $this->validate($this->profileValidator, $user, $id, 'actualizar', 'perfil', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->user::find($id);
                if(!empty($sql)) {
                    $sql->password = Hash::make($user['password']);
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Perfil actualizado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar el perfil',
                                'detail' => 'El usuario no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el perfil',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function updatePushToken(string $token, int $id){
            try {
                $sql = $this->user::find($id);
                if(!empty($sql)) {
                    $sql->push_token = $token;
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Token push actualizado con exito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar el token push',
                                'detail' => 'El usuario no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el token push',
                            'detail' => $e->getMessage(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>