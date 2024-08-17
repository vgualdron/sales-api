<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\DiaryServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Diary;
    use App\Validator\{DiaryValidator};
    use App\Traits\Commons;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\DB;
    
    class DiaryServiceImplement implements DiaryServiceInterface {

        use Commons;

        private $diary;
        private $validator;

        function __construct(DiaryValidator $validator){
            $this->diary = new Diary;
            $this->validator = $validator;
        }    

        function list(string $date, int $user, string $moment) {
            try {
                $dates = $this->getDatesOfWeek($date, $moment);
                 
                $sql = $this->diary->from('diaries as d')
                    ->select(
                        'd.id',
                        'd.user_id',
                        'u.name as userName',
                        'd.date',
                        'd.new_id',
                        'd.status',
                        'd.observation',
                    )
                    ->leftJoin('users as u', 'd.user_id', 'u.id')
                    ->leftJoin('news as n', 'd.new_id', 'n.id')
                    ->where('user_id', $user)
                    ->whereBetween('date', ["$dates[0] 00:00:00", "$dates[5] 23:59:59"])
                    ->orderBy('date', 'ASC')
                    ->get();

                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'data' => [],
                    ], Response::HTTP_OK);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los registros',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function listDayByDay(string $date, int $user, string $moment) {
            try {
                $dates = $this->getDatesOfWeek($date, $moment);
                $days = $this->getHoursOfDay();
                $data = [];
                foreach ($days as $i => $valueDay) {
                    foreach ($dates as $j => $valueDate) {
                        $sql = $this->diary->from('diaries as d')
                        ->select(
                            'd.id',
                            'd.user_id',
                            'u.name as userName',
                            'd.date',
                            'd.new_id',
                            'n.name as new_name',
                            'n.address as new_address',
                            'n.address_work',
                            'n.address_house',
                            'n.site_visit',
                            'n.district as new_district',
                            'n.occupation as new_occupation',
                            'n.phone as new_phone',
                            'd.status',
                            'd.observation',
                            's.name as sectorName',
                        )
                        ->leftJoin('users as u', 'd.user_id', 'u.id')
                        ->leftJoin('news as n', 'd.new_id', 'n.id')
                        ->leftJoin('yards as s', 'n.sector', 's.id')
                        ->where('user_id', $user)
                        ->where('date', "$valueDate $valueDay")
                        ->orderBy('date', 'ASC')
                        ->first();
                        
                        if ($sql) {
                            $data[$i]["date"] = $valueDate;
                            if ($j == 0) {
                                $data[$i]["items"][$j] = $sql;
                            }
                            $data[$i]["items"][$j + 1] = $sql;
                        } else {
                            $data[] = [];
                        }
                    }
                }
                 
                return response()->json([
                    'data' => $data
                ], Response::HTTP_OK);
                
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los registros',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function listVisitsReview(string $date) {
            try {
                $sql = $this->diary->from('diaries as d')
                ->select(
                    'd.id',
                    'd.user_id',
                    'u.name as userName',
                    'd.date',
                    'd.new_id',
                    'n.name as new_name',
                    'n.address as new_address',
                    'n.address_work',
                    'n.address_house',
                    'n.site_visit',
                    'n.district as new_district',
                    'n.occupation as new_occupation',
                    'n.phone as new_phone',
                    'd.status',
                    'd.observation',
                    's.name as sectorName',
                )
                ->join('users as u', 'd.user_id', 'u.id')
                ->join('news as n', 'd.new_id', 'n.id')
                ->leftJoin('yards as s', 'n.sector', 's.id')
                ->where('date', ">=", "$date 00:00:00")
                ->where('date', "<=", "$date 23:59:59")
                ->orderBy('date', 'ASC')
                ->get();
                
                if (count($sql) > 0){
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'data' => [],
                    ], Response::HTTP_OK);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los registros',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function getStatusCases(int $idNew) {
            try {
                $data = [];
                $sql = $this->diary->from('news as n')
                ->select(
                    'n.*'
                )->where('id', "=", $idNew)
                ->get()
                ->first();

                $files = $this->diary->from('files as f')
                ->select(
                    'f.*'
                )->where('model_id', "=", $idNew)
                ->where('model_name', "=", "news")
                ->get();

                $data['dataClient']['name'] = $sql->name ? true : false;
                $data['dataClient']['documentNumber'] = $sql->document_number ? true : false;
                $data['dataClient']['addressHouse'] = $sql->address_house ? true : false;
                $data['dataClient']['addressWork'] = $sql->address_work ? true : false;
                $data['dataClient']['occupation'] = $sql->occupation ? true : false;
                $data['dataClient']['typeHouse'] = $sql->type_house ? true : false;
                $data['dataClient']['typeWork'] = $sql->type_work ? true : false;
                $data['dataClient']['quantity'] = $sql->quantity ? true : false;
                $data['dataClient']['period'] = $sql->period ? true : false;

                $data['dataRef1']['name'] = $sql->family_reference_name ? true : false;
                $data['dataRef1']['address'] = $sql->family_reference_address ? true : false;
                $data['dataRef1']['phone'] = $sql->family_reference_phone ? true : false;
                $data['dataRef1']['relationship'] = $sql->family_reference_relationship ? true : false;

                $data['dataRef2']['name'] = $sql->family2_reference_name ? true : false;
                $data['dataRef2']['address'] = $sql->family2_reference_address ? true : false;
                $data['dataRef2']['phone'] = $sql->family2_reference_phone ? true : false;
                $data['dataRef2']['relationship'] = $sql->family2_reference_relationship ? true : false;

                $data['dataGuarantor']['name'] = $sql->guarantor_name ? true : false;
                $data['dataGuarantor']['address'] = $sql->guarantor_address ? true : false;
                $data['dataGuarantor']['phone'] = $sql->phone ? true : false;
                $data['dataGuarantor']['relationship'] = $sql->relationship ? true : false;
                
                $data['files'] = $files;
                // $data['files']['name'] = $sql2->name ? true : false;

                $nameCertTrabClient = "FOTO_CERTIFICADO_TRABAJO_CLIENTE";
                $data['trabajo'][$nameCertTrabClient] = $files->first(function($file) {
                    return $file["name"] == $nameCertTrabClient;
                });

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

                return response()->json([
                    'data' => $data
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los registros',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function create(array $diary) {
            try {
                $validation = $this->validate($this->validator, $diary, null, 'registrar', 'diario', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                
                DB::transaction(function () use ($diary) {
                    $dates = $this->getDatesOfWeek($diary['date'], $diary['moment']);
                    $hours = $this->getHoursOfDay();
                    foreach ($dates as $i => $value) {
                        foreach ($hours as $j => $hour) {
                            $sql = $this->diary::create([
                                'date' => "$value $hour",
                                'user_id' => $diary['userId'],
                            ]);
                        }
                    }
                });
                
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Agenda registrado con éxito',
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

        function update(array $diary, int $id){
            try {
                $validation = $this->validate($this->validator, $diary, $id, 'actualizar', 'agenda', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->diary::find($id);
                if(!empty($sql)) {
                    DB::transaction(function () use ($sql, $diary) {
                        $sql->status = $diary['status'];
                        $sql->new_id = $diary['new_id'];
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
                            'text' => 'Advertencia al actualizar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function updateStatus(array $novel, int $id){
            try {
                $validation = $this->validate($this->validator, $novel, $id, 'actualizar', 'nuevo', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->novel::find($id);
                if(!empty($sql)) {
                     DB::transaction(function () use ($sql, $novel) {
                        $sql->document_number = $novel['documentNumber'];
                        $sql->name = $novel['name'];
                        $sql->phone = $novel['phone'];
                        $sql->address = $novel['address'];
                        $sql->sector = $novel['sector'];
                        $sql->status = $novel['status'];
                        $sql->attempts = $novel['attempts'];
                        $sql->district = $novel['district'];
                        $sql->occupation = $novel['occupation'];
                        $sql->observation = $novel['observation'];
                        $sql->user_send = $novel['userSend'] ? $novel['userSend'] : null;
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
                            'text' => 'Advertencia al actualizar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->novel::find($id);
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
                                'text' => 'Advertencia al eliminar el registro',
                                'detail' => 'El registro no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el registro',
                                'detail' => $e->getMessage()
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar',
                                'detail' => $e->getMessage()
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->diary::select(
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
                                'text' => 'El registro no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar',
                            'detail' => $e->getMessage()
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        function getDatesOfWeek($date, $moment = 'current') {
            $fecha = strtotime($date);
            $weekNumber = date("W", $fecha);
            if ($moment == 'next') {
                $weekNumber += 1;
            }
            $year = date("Y", $fecha);
            
            $dates = [];
        
            // Crear una fecha para el primer día de la semana específica
            $firstDayOfWeek = strtotime($year . "W" . str_pad($weekNumber, 2, '0', STR_PAD_LEFT));
        
            // Obtener las fechas de la semana
            for ($i = 0; $i < 6; $i++) {
                $dates[] = date('Y-m-d', strtotime("+$i day", $firstDayOfWeek));
            }
        
            return $dates;
        }
        
        function getHoursOfDay() {
            $dates[0] = "08:00:00";
            $dates[1] = "09:00:00";
            $dates[2] = "10:00:00";
            $dates[3] = "11:00:00";
            $dates[4] = "14:00:00";
            $dates[5] = "15:00:00";
            $dates[6] = "16:00:00";
            $dates[7] = "17:00:00";
            return $dates;
        }

    }
?>