<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Validation\Rule;

    class RateValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'movement' => 'required|in:T,C,V',
                'originYard' => 'nullable|required_if:movement,T,V|exists:yards,id|not_in:'.(isset($this->data['destinyYard']) ? $this->data['destinyYard']: null),
                'destinyYard' => 'nullable|required_if:movement,T,C|exists:yards,id|not_in:'.(isset($this->data['originYard']) ? $this->data['originYard']: null),
                'supplier' => 'nullable|required_if:movement,C|exists:thirds,id',
                'customer' => 'nullable|required_if:movement,V|exists:thirds,id',
                'startDate' => 'required|date|before_or_equal:finalDate',
                'finalDate' => 'required|date|after_or_equal:startDate',
                'material' => 'required|exists:materials,id',
                'conveyorCompany' => 'nullable|required_if:movement,T|exists:thirds,id',
                'materialPrice' => 'nullable|required_if:movement,C|numeric|gte:0',
                'freightPrice' => 'required|numeric|gte:0',
                'totalPrice' => 'nullable|required_if:movement,C,V|numeric|gte:0',
                'observation' => 'nullable|max:600',
                'roundTrip' => 'required_if:movement,T|boolean'
            ];
        }

        private function messages(){
            return [
                'movement.required' => 'El tipo de movimiento es requerido',
                'movement.in' => 'El tipo de movimiento debe ser de tipo T:Traslado, C: Compra o V:Venta',
                'originYard.required_if' => 'El patio de despacho es requerido',
                'originYard.exists' => 'El patio de despacho ingresado no existe',
                'originYard.not_in' => 'El patio de despacho debe ser diferente al patio de destino',
                'destinyYard.required_if' => 'El patio de destino es requerido',
                'destinyYard.exists' => 'El patio de destino ingresado no existe',
                'destinyYard.not_in' => 'El patio de destino debe ser diferente al patio de despacho',
                'supplier.required_if' => 'El proveedor es requerido',
                'supplier.exists' => 'El proveedor ingresado no existe',
                'customer.required_if' => 'El cliente es requerido',
                'customer.exists' => 'El cliente ingresado no existe',
                'startDate.required' => 'La fecha de inicio es requerida',
                'startDate.date' => 'La fecha de inicio ingresada no es una fecha válida',
                'startDate.before_or_equal' => 'La fecha de inicio debe ser menor o igual a la fecha final',
                'finalDate.required' => 'La fecha final es requerida',
                'finalDate.date' => 'La fecha final ingresada no es una fecha válida',
                'finalDate.after_or_equal' => 'La fecha final debe ser mayor o igual a la fecha de inicio',
                'material.required' => 'El material es requerido',
                'material.exists' => 'El material ingresado no existe',
                'conveyorCompany.required_if' => 'La empresa transportadora es requerida',
                'conveyorCompany.exists' => 'La empresa transportadora ingresada no existe',
                'materialPrice.required_if' => 'El valor del material es requerido',
                'materialPrice.numeric' => 'El valor del material debe ser de tipo numérico',
                'materialPrice.gte' => 'El valor del material no debe ser negativo',
                'freightPrice.required' => 'El valor del flete es requerido',
                'freightPrice.numeric' => 'El valor del flete debe ser de tipo numérico',
                'freightPrice.gte' => 'El valor del flete no debe ser negativo',
                'totalPrice.required_if' => 'El valor total es requerido',
                'totalPrice.numeric' => 'El valor total debe ser de tipo numérico',
                'totalPrice.gte' => 'El valor total no debe ser negativo',
                'observation.max' => 'La observacion debe tener un máximo de 600 caracteres',
                'roundTrip.required_if' => 'Debe indicar si la tarifa corresponde a un viaje redondo o no',
                'roundTrip.boolean' => 'Debe usar alguno de los siguientes valores para indicar si la tarida corresponde a un viaje redondo o no (true, false, 0, 1, "0" o "1")'
            ];
        }
    }
?>