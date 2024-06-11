<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class AdjustmentValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'type' => 'required',
                'yard' => 'required|exists:yards,id',
                'material' => 'required|exists:materials,id',
                'amount' => 'required|numeric|gt:0|lt:100000000',
                'date' => 'required|date',
                'observation' => 'nullable|max:600'
            ];
        }

        private function messages(){
            return [
                'type.required' => 'El tipo es requerido',
                'yard.required' => 'Debe ingresar un patio',
                'yard.exists' => 'El patio ingresado no existe',
                'material.required' => 'El patio ingresado no existe',
                'material.exists' => 'El material ingresado no existe',
                'amount.required' => 'Debe ingresar una cantidad',
                'amount.numeric' => 'La cantidad debe ser de tipo numérico',
                'amount.gt' => 'La cantidad debe ser mayor a 0',
                'amount.lt' => 'La cantidad debe ser menor a 100000000',
                'date.required' => 'Debe ingresar una fecha',
                'date.date' => 'La fecha ingresada no es válida',
                'observation.max' => 'La observación debe tener un máximo de 600 caracteres'
            ];
        }
    }
?>