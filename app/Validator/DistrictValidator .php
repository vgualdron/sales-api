<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class DistrictValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'name' => 'required|min:4|max:300',
                'sector' => 'required|exists:yards,id',
                'group' => 'required',
                'order' => 'required',
            ];
        }

        private function messages(){
            return [
                'name.required' => 'El nombre es requerido',
                'name.min' => 'El nombre debe tener un mínimo de 4 caracteres',
                'name.max' => 'El nombre debe tener un máximo de 300 caracteres',
                'sector.required' => 'El sector es requerido',
                'sector.exists' => 'El sector seleccionado no existe',
                'group.required' => 'El grupo es requerido',
                'order.required' => 'El orden es requerido',
            ];
        }
    }
?>