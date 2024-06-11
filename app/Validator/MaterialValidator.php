<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class MaterialValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'code' => 'required|min:2|max:10|unique:materials,code,'.$this->data['id'],
                'name' => 'required|min:4|max:70|unique:materials,name,'.$this->data['id'],
                'unit' => 'required',
                'active' => 'required|boolean'
            ];
        }

        private function messages(){
            return [
                'code.required' => 'El código es requerido',
                'code.unique' => 'El código "'.$this->data['code'].'", ya existe',               
                'code.min' => 'El código debe tener un mínimo de 2 caracteres',
                'code.max' => 'El código debe tener un máximo de 10 caracteres',
                'name.required' => 'El nombre es requerido',
                'name.unique' => 'El nombre "'.$this->data['name'].'", ya existe',               
                'name.min' => 'El nombre debe tener un mínimo de 4 caracteres',
                'name.max' => 'El nombre debe tener un máximo de 70 caracteres',
                'unit.required' => 'La unidad es requerida',
                'active.required' => 'Debe indicar si el usuario se encuentra activo o inactivo',
                'active.boolean' => 'Debe usar alguno de los siguientes valores para indicar si el usuario se encuentra activo o no (true, false, 0, 1, "0" o "1")',
            ];
        }
    }
?>