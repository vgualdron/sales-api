<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class ExpenseValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[                             
                'amount' => 'required',
                'date' => 'required',
            ];
        }

        private function messages(){
            return [
                'amount.required' => 'La cantidad es requerida',
                'date.required' => 'La fecha es requerida',
            ];
        }
    }
?>