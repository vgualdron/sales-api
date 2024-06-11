<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class RoleValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                "name" => "required|min:5|max:30|unique:roles,name,".$this->data['id'],
                'permissions.*' => 'nullable|exists:permissions,id',
            ];
        }

        private function messages(){
            return [
                'name.required' => 'El nombre es requerido',
                'name.unique' => 'El nombre "'.$this->data['name'].'", ya existe',
                'name.min' => 'El nombre debe tener un mínimo de 5 caracteres',
                'name.max' => 'El nombre debe tener un máximo de 30 caracteres',
                'permissions.*' => 'Uno o varios de los permisos seleccionados no existen'
            ];
        }
    }
?>