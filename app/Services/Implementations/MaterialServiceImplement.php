<?php
    namespace App\Services\Implementations;
    use App\Services\Interfaces\MaterialServiceInterface;
    use Symfony\Component\HttpFoundation\Response;
    use App\Models\Material;
    use App\Models\Yard;
    use App\Models\Ticket;
    use App\Models\Adjustment;
    use App\Validator\MaterialValidator;
    use App\Traits\Commons;
    use Illuminate\Database\Query\JoinClause;
    use Illuminate\Support\Facades\DB;
    
    class MaterialServiceImplement implements MaterialServiceInterface {

        use Commons;

        private $material;
        private $yard;
        private $ticket;
        private $adjustment;
        private $validator;

        function __construct(MaterialValidator $validator){
            $this->material = new Material;
            $this->yard = new Yard;
            $this->ticket = new Ticket;
            $this->adjustment = new Adjustment;
            $this->validator = $validator;
        }    

        function list(int $displayAll, string $material){
            try {
                $sql = $this->material->select('id', 'code', 'name', 'unit', 'active')
                    ->when($displayAll === 0, function ($query) use ($material)  {
                        $material = explode(',', $material);
                        return $query->where('active', 1)
                            ->orWhereIn('id', $material);
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
                                'text' => 'No hay materiales para mostrar',
                                'detail' => 'Aun no ha registrado ningun material'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al cargar los materiales',
                            'detail' => 'intente recargando la página'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }


        function create(array $material){
            try {
                $validation = $this->validate($this->validator, $material, null, 'registrar', 'material', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $status = $this->material::create([
                    'code' => $material['code'],
                    'name' => $material['name'],
                    'unit' => $material['unit'],
                    'active' => $material['active']
                ]);
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Material registrado con éxito',
                            'detail' => null
                        ]
                    ]
                ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al registrar el material',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function update(array $material, int $id){
            try {
                $validation = $this->validate($this->validator, $material, $id, 'actualizar', 'material', null);
                if ($validation['success'] === false) {
                    return response()->json([
                        'message' => $validation['message']
                    ], Response::HTTP_BAD_REQUEST);
                }
                $sql = $this->material::find($id);
                if(!empty($sql)) {
                    $sql->name = $material['name'];
                    $sql->code = $material['code'];
                    $sql->unit = $material['unit'];
                    $sql->active = $material['active'];
                    $sql->save();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Material actualizado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al actualizar el material',
                                'detail' => 'El material no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Advertencia al actualizar el material',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function delete(int $id){   
            try {
                $sql = $this->material::find($id);
                if(!empty($sql)) {
                    $sql->delete();
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Material eliminado con éxito',
                                'detail' => null
                            ]
                        ]
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el material',
                                'detail' => 'El material no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                if ($e->getCode() !== "23000") {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Advertencia al eliminar el material',
                                'detail' => 'Si este problema persiste, contacte con un administrador'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'No se permite eliminar el registro',
                                'detail' => 'El material se encuentra asociada a otro registro'
                            ]
                        ]
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        function get(int $id){
            try {
                $sql = $this->material::select('id', 'code', 'name', 'unit', 'active')
                            ->where('id', $id)   
                            ->first();
                if(!empty($sql)) {
                    return response()->json([
                        'data' => $sql
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'El material no existe',
                                'detail' => 'por favor recargue la página'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar el material',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        function getMaterialsByYard(int $yard){
            try {
                $existsYard = $this->yard::select(
                    'id'
                )
                ->where('id', $yard)
                ->first();
                if (is_null($existsYard)) {
                    return response()->json([
                        'message' => [
                            [
                                'text' => 'Se ha presentado un problema al buscar los materiales',
                                'detail' => 'El patio seleccionado no existe'
                            ]
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }
                $ticketsOut = $this->ticket::from('tickets as t')
                    ->select(
                        'y.id as yard',
                        'y.name as yardName',
                        'm.id as material',
                        'm.name as materialName',
                        't.type as type',
                        't.net_weight as amount',
                        DB::Raw('"T" as unit')
                    )
                    ->join('yards as y', 't.origin_yard', 'y.id')
                    ->join('materials as m', 't.material', 'm.id')
                    ->where(function ($query) {
                        $query->where('type', 'D')
                            ->orWhere('type', 'V');
                        }
                    )
                    ->where('m.unit', 'T')
                    ->where('y.id', $yard);
               
                $ticketsIn = $this->ticket::from('tickets as t')
                    ->select(
                        'y.id as yard',
                        'y.name as yardName',
                        'm.id as material',
                        'm.name as materialName',
                        't.type as type',
                        't.net_weight as amount',
                        DB::Raw('"T" as unit')
                    )
                    ->join('yards as y', 't.destiny_yard', 'y.id')
                    ->join('materials as m', 't.material', 'm.id')
                    ->where(function ($query) {
                        $query->where('type', 'R')
                            ->orWhere('type', 'C');
                        }
                    )
                    ->where('m.unit', 'T')
                    ->where('y.id', $yard);
                  
                $adjustment = $this->adjustment::from('adjustments as a')
                    ->select(
                        'y.id as yard',
                        'y.name as yardName',
                        'm.id as material',
                        'm.name as materialName',
                        'a.type as type',
                        'a.amount as amount',
                        DB::Raw('"T" as unit')
                    )
                    ->join('yards as y', 'a.yard', 'y.id')
                    ->join('materials as m', 'a.material', 'm.id')
                    ->where('m.unit', 'T')
                    ->where('y.id', $yard)
                    ->union($ticketsOut)
                    ->union($ticketsIn);
                                    
                $stocks = DB::table($adjustment)
                    ->select(
                        'material',
                        DB::Raw('FORMAT(SUM(IF(type = "C" OR type = "R" OR type = "A", amount, amount*(-1))), 2) as amount'),
                        'unit'
                    )
                    ->groupBy('yard', 'material');
                
                $materials = $this->material::from('materials as m')
                    ->select(
                        'm.id as material',
                        'm.code as code',
                        'm.name as name',
                        'm.unit as unit',
                        DB::Raw('COALESCE(s.amount ,0) as amount')
                    )
                    ->leftJoinSub($stocks, 's', function (JoinClause $join) {
                        $join->on('m.id', 's.material');
                    })
                    ->where('m.unit', 'T')
                    ->get();
                    return response()->json([
                        'data' => $materials
                    ], Response::HTTP_OK);
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => [
                        [
                            'text' => 'Se ha presentado un error al buscar los materiales',
                            'detail' => 'Si este problema persiste, contacte con un administrador'
                        ]
                    ]
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
?>