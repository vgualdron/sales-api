<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\AuthServiceInterface;
    use Illuminate\Support\Facades\{Hash, Auth};
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\{
        OauthClient,
        User,
        OauthAccessToken,
    };
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\DB;

    class AuthServiceImplement implements AuthServiceInterface{

        private $oauthClient;
        private $oauthAccessToken;
        private $user;

        function __construct(){
            $this->oauthClient = new OauthClient;
            $this->user = new User;
            $this->oauthAccessToken = new OauthAccessToken;
        }

        function getActiveToken(){
            try {
                $sql = $this->oauthClient->select('secret as key')
                            ->where('password_client', 1)
                            ->where('revoked', 0)
                            ->first();

                $oauthClient = !empty($sql) ? $sql->key : null;

                if (!empty($oauthClient)){
                    return response()->json([
                        'key' => $oauthClient
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => ['Actualmente no es posible iniciar sesión, por favor contacte con un administrador']
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => ['Se ha presentado un error al preparar el inicio de sesión, por favor contacte con un administrador']
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function login(string $documentNumber, string $password){
            try {
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('optimize:clear');
                $user = $this->user::where('document_number', $documentNumber)->where('active', 1)->first();
                if (!empty($user)) {
                    if ($user->active === 1) {
                        if(Auth::attempt(['document_number' => $documentNumber, 'password' => $password])){
                            $grantClient = $this->oauthClient->select('secret as key')
                                ->where('password_client', 1)
                                ->where('revoked', 0)
                                ->first();

                            $grantClient = !empty($grantClient) ? $grantClient->key : null;

                            if (!empty($grantClient)) {
                                $this->oauthAccessToken::where('user_id', '=', $user->id)
                                    ->delete();
                                $token = $user->createToken($grantClient)->accessToken;
                                // $permissions = $user->getPermissionsViaRoles();
                                $permissions = User::from('users as u')
                                ->select(
                                    'p.id as id',
                                    'p.name as name',
                                    'p.guard_name as guard_name',
                                    'p.display_name as display_name',
                                    'p.group as group',
                                    'p.route as route',
                                    'p.menu as menu',
                                    'g.name as group_name',
                                    'g.icon as group_icon',
                                    'g.label as group_label',
                                    'g.id as group_id',
                                )
                                ->join('model_has_roles as mhr', 'u.id', 'mhr.model_id')
                                ->join('role_has_permissions as rhp', 'mhr.role_id', 'rhp.role_id')
                                ->join('permissions as p', 'rhp.permission_id', 'p.id')
                                ->join('groups as g', 'p.group_id', 'g.id')
                                ->where('u.id', $user->id)
                                ->orderBy('g.order_number', 'ASC')
                                ->orderBy('p.order', 'ASC')
                                ->get();

                                $roles = $user->getRoleNames();
                                $dataPermissions = [];
                                $menu = [];

                                foreach ($permissions as $permission) {
                                    $menu[$permission->group_id]['name'] = $permission->group_name;
                                    $menu[$permission->group_id]['label'] = $permission->group_label;
                                    $menu[$permission->group_id]['icon'] = $permission->group_icon;
                                    $menu[$permission->group_id]['options'][] = [
                                        'route' => $permission->route,
                                        'name' => $permission->group,
                                        'menu' => $permission->menu
                                    ];
                                    $dataPermissions[] = [
                                        'name' => $permission->name,
                                        'displayName' => $permission->display_name
                                    ];
                                }

                                $menu = array_values($menu);

                                foreach ($menu as $index => $item) {
                                    $menu[$index]['options'] = array_values(array_unique($item['options'], SORT_REGULAR));
                                }

                                $userObject = User::from('users as u')
                                    ->select(
                                        'u.*',
                                        'f.url as url_photo_profile'
                                    )
                                    ->leftJoin('files as f', function ($join) {
                                        $join->on('f.model_id', '=', 'u.id')
                                            ->where('f.model_name', '=', 'users')
                                            ->where('f.name', '=', 'FOTO_PROFILE');
                                    })
                                    ->where('u.id', $user->id)
                                    ->groupBy('u.id', 'f.url')
                                    ->orderBy('u.id', 'ASC')
                                    ->first();

                                $userData = array(
                                    'name' => $user->name,
                                    'type_document' => $user->type_document,
                                    'document' => $user->document_number,
                                    'created_at' => $user->created_at,
                                    'updated_at' => $user->updated_at,
                                    'user_id' => $userObject->id,
                                );

                                if($userObject) {
                                    $userData["user_url_photo_proile"] = $userObject->url_photo_profile;
                                    $userData["update_photo"] = $userObject->updatePhoto;
                                    $userData["update_password"] = $userObject->updatePassword;
                                    $userData["completed_fields"] = $userObject->completedFields;
                                }

                                $rolesArray = User::from('users as u')
                                ->select(
                                    'r.id as id',
                                    'r.name as name',
                                    'r.route as route'
                                )
                                ->join('model_has_roles as mhr', 'u.id', 'mhr.model_id')
                                ->join('roles as r', 'r.id', 'mhr.role_id')
                                ->where('u.id', $user->id)
                                ->orderBy('r.id', 'ASC')
                                ->get();

                                return response()->json([
                                    'token' => $token,
                                    'user' => $userData,
                                    'permissions' => array_values(array_unique($dataPermissions, SORT_REGULAR)),
                                    'menu' => array_values(array_unique($menu, SORT_REGULAR)),
                                    'roles' => $roles,
                                    'rolesArray' => $rolesArray,
                                ], Response::HTTP_OK);
                            } else {
                                return response()->json([
                                    'message' => [
                                        [
                                            'text' => 'Error de autenticación',
                                            'detail' => 'Se ha presentado un inconveniente al generar el token de sesión'
                                        ]
                                    ]
                                ], Response::HTTP_NOT_FOUND);
                            }
                        } else {
                            return response()->json([
                                'message' => [
                                    [
                                        'text' => 'Error de autenticación',
                                        'detail' => 'La contraseña ingresada es incorrecta'
                                    ]
                                ]
                            ], Response::HTTP_NOT_FOUND);
                        }
                    } else {
                        return response()->json([
                            'message' => [
                                [
                                    'text' => 'Error de autenticación',
                                    'detail' => 'El usuario con el número de documento "'.$documentNumber.'" se encuentra inactivo'
                                ]
                            ]
                        ], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Error de autenticación',
                                'detail' => 'El usuario con el número de documento "'.$documentNumber.'" no se encuentra registrado'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $t) {
                // dd($t->getMessage().' '.$t->getLine());
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error en el servicio',
                            'detail' => $t->getMessage().' '.$t->getLine(),
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function logout(){
            try {
                $id = Auth::user()->id;
                $this->oauthAccessToken::where('user_id', '=', $id)
                    ->delete();
                return response()->json([], Response::HTTP_OK);
            } catch (\Throwable $t) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error en el servicio',
                            'detail' => 'por favor, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>
