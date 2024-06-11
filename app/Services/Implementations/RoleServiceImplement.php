<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\RoleServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\{Role, Permission, User};
    use Spatie\Permission\Models\Role as SPRole;
    use App\Validator\RoleValidator;
    use App\Traits\Commons;
    use Illuminate\Support\Facades\DB;
    
    class RoleServiceImplement implements RoleServiceInterface {

        use Commons;

        private $role;
        private $spRole;
        private $permission;
        private $validator;
        private $user;

        function __construct(RoleValidator $validator){
            $this->role = new Role;
            $this->spRole = new SPRole;
            $this->user = new User;
            $this->permission = new Permission;
            $this->validator = $validator;
        }    

        function list(){
            try {
                $sql = $this->role->select('id', 'name')
                            ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay roles para mostrar',
                                'detail' => 'Aun no ha registrado ningun rol'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los roles',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $role){
            try {
                $validation = $this->validate($this->validator, $role, null, 'registrar', 'rol', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                DB::transaction(function () use ($role) {
                    $status = $this->spRole::create([
                        'name' => $role['name'],
                        'guard_name' => 'api',
                        'editable' => 1
                    ]);
                    $status->syncPermissions($role['permissions']);
                });
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Rol registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar el rol',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $role, int $id){
            try {
                $validation = $this->validate($this->validator, $role, $id, 'actualizar', 'rol', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->role::find($id);
                if(!empty($sql)) {
                    if($sql->editable !== 0) {
                        DB::transaction(function () use ($role, $id) {
                            $status = $this->spRole::find($id);                            
                            $status->name = $role['name'];
                            $status->save();
                            $status->syncPermissions($role['permissions']);
                        });
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Rol actualizado con éxito',
                                    'detail' => null
                                ]
                            ]
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Advertencia al actualizar el rol',
                                    'detail' => 'No se permite editar el rol "'.$sql->name.'"'
                                ]
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar el rol',
                                'detail' => 'El rol no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el rol',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){
            try {
                $sql = $this->role::find($id);
                if(!empty($sql)) {
                    if($sql->editable === 1) {
                        $role = $this->spRole::findById($id);
                        $users = User::role($role->name)->get();
                        if (count($users) === 0) {
                            $sql->delete();
                            return response()->json([
                                'message' => [
                                    [
                                        'text' => 'Rol eliminado con éxito',
                                        'detail' => null
                                    ]
                                ]
                            ], Response::HTTP_OK);
                        } else {
                            return response()->json([
                                'message' => [
                                    [
                                        'text' => 'No se permite eliminar este registro',
                                        'detail' => 'El rol "'.$sql->name.'" se encuentra asociado a uno o más usuarios'
                                    ]
                                ]
                            ], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    } else {
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Advertencia al eliminar el rol',
                                    'detail' => 'No se permite eliminar el rol "'.$sql->name.'"'
                                ]
                            ]
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el rol',
                                'detail' => 'El rol no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al eliminar el rol',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function get(int $id){
            try {
                $sql = $this->role::select('id', 'name', 'editable', DB::Raw('NULL as permissions'))
                            ->where('id', $id)   
                            ->first();
                if(!empty($sql)) {
                    $role = $this->spRole::findById($id);
                    $rolePermissions = $role->permissions->pluck('id');
                    $sql->permissions = $rolePermissions;
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'El rol no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar el rol',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>