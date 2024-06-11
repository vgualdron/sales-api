<?php
    namespace App\Traits;

    trait Commons
    {
        public function validate($validator, $data, $id, $action, $model, $line) {
            $dataReturn = ['success' => true, 'message' => []];
            $validation = $validator->validate($data, $id);
            if ($validation->fails()) {
                $dataReturn['success'] = false;
                $message = [];
                foreach ($validation->errors()->get('*') as $item) {
                    $message[] = [
                            'text' => 'Advertencia al '.$action.' '.$model,
                            'detail' => $item[0].($line === null ? '' : (', en la línea '.$line.' del archivo'))
                        ];
                }
                $dataReturn['message'] = $message;
            }
            return $dataReturn;
        }
    }  
?>