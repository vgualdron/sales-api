<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class ThirdValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'nit' => 'required|min:2|max:50|unique:thirds,nit,'.$this->data['id'],
                'name' => 'required|min:4|max:200|unique:thirds,name,'.$this->data['id'],
                'customer' => 'required',
                'associated' => 'required',
                'contractor' => 'required',
                'active' => 'required|boolean'
            ];
        }

        private function messages(){
            return [
                'nit.required' => 'El NIT es requerido',
                'nit.unique' => 'El NIT "'.$this->data['nit'].'", ya existe',               
                'nit.min' => 'El NIT debe tener un mínimo de 2 caracteres',
                'nit.max' => 'El NIT debe tener un máximo de 50 caracteres',
                'name.required' => 'El nombre es requerido',
                'name.unique' => 'El nombre "'.$this->data['name'].'", ya existe',               
                'name.min' => 'El nombre debe tener un mínimo de 4 caracteres',
                'name.max' => 'El nombre debe tener un máximo de 200 caracteres',
                'active.required' => 'Debe indicar si el usuario se encuentra activo o inactivo',
                'active.boolean' => 'Debe usar alguno de los siguientes valores para indicar si el usuario se encuentra activo o no (true, false, 0, 1, "0" o "1")',
                'customer.required' => 'Debe indicar si el tercero es de tipo cliente o no',
                'associated.required' => 'Debe indicar si el tercero es de tipo asociado o no',
                'contractor.required' => 'Debe indicar si el tercero es de tipo contratista o no'
            ];
        }
    }
?>