<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\PermissionServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Permission;
    
    class PermissionServiceImplement implements PermissionServiceInterface {

        private $permission;

        function __construct(){
            $this->permission = new Permission;
        }    

        function list(){
            try {
                $sql = $this->permission::select('id', 'display_name', 'group')
                    ->get();
                if (count($sql) > 0){
                    $permissions = [];
                    foreach ($sql as $permission) {
                        $permissions[$permission->group][] = [
                            'value' => $permission->id,
                            'label' => $permission->display_name
                        ];
                    }
                    return response()->json([
                        'data' => $permissions
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No hay permisos para mostrar',
                                'detail' => 'Por favor, contacte con un administrador'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los permisos',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>