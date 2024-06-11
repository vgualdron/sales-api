<?php
    namespace App\Validator;
    use Illuminate\Support\Facades\Validator;

    class TicketValidator{

        private $data;

        public function validate($data, $id){
            $this->data = $data;
            $this->data['id'] = $id;
            $this->data['referralNumber'] = (!isset($this->data['referralNumber']) || $this->data['type'] === 'C' || trim($this->data['referralNumber']) === '') ? null : $this->data['referralNumber'];
            $this->data['receiptNumber'] = (!isset($this->data['receiptNumber']) || in_array($this->data['type'], ['D', 'V']) === true || trim($this->data['receiptNumber']) === '') ? null : $this->data['receiptNumber'];
            return Validator::make($this->data, $this->rules(), $this->messages());            
        }

        private function rules(){
            return[
                'type' => 'required|in:D,R,C,V',
                'user' => 'required|exists:users,id',
                'date' => 'required|date',
                'time' => 'required|date_format:H:i',
                'licensePlate' => 'required|max:30',
                'trailerNumber' => 'nullable|max:30',
                'referralNumber' => 'nullable|required_if:type,D,R,V|max:30|unique:tickets,referral_number,'.$this->data['id'].',id,type,'.$this->data['type'],
                'receiptNumber' => 'nullable|required_if:type,R,C|max:30|unique:tickets,receipt_number,'.$this->data['id'].',id,type,'.$this->data['type'],
                'originYard' => 'nullable|required_if:type,D,R,V|exists:yards,id|not_in:'.(isset($this->data['destinyYard']) ? $this->data['destinyYard']: null),
                'destinyYard' => 'nullable|required_if:type,D,R,C|exists:yards,id|not_in:'.(isset($this->data['originYard']) ? $this->data['originYard']: null),
                'customer' => 'nullable|required_if:type,V|exists:thirds,id',
                'supplier' => 'nullable|required_if:type,C|exists:thirds,id',
                'conveyorCompany' => 'nullable|required_if:type,D,R|exists:thirds,id',
                'driverName' => 'required|max:100',
                'driverDocument' => 'required|max:20',
                'material' => 'required|exists:materials,id',
                'ashPercentage' => 'nullable|numeric|gte:0|lt:100',
                'grossWeight' => 'required|numeric|gt:0|lt:100000000',
                'tareWeight' => 'required|numeric|gt:0|lt:100000000',
                'netWeight' => 'required|numeric|gt:0|lt:100000000',
                'seals' => 'nullable|max:200',
                'observation' => 'nullable|max:600',
                'roundTrip' => 'required_if:type,D,R|boolean'
            ];
        }

        private function messages(){
            $type = '';
            switch ($this->data['type']) {
                case 'D':
                    $type = 'un despacho';
                    break;
                case 'R':
                    $type = 'una recepción';
                    break;
                case 'C':
                    $type = 'una compra';
                    break;
                case 'V':
                    $type = 'una venta';
                    break;
            }
            return [
                'type.required' => 'El tipo de movimiento es requerido',
                'type.in' => 'El tipo de movimiento debe corresponder a uno de los siguientes valores: D (Despacho), R (Recepción), C (Compra) o V (Venta)',
                'user.required' => 'Debe indicar el usuario que registra el tiquete',
                'user.exists' => 'El usuario ingresado no existe',
                'date.required' => 'La fecha es requerida',
                'date.date' => 'La fecha ingresada no tiene un formato válido',
                'time.required' => 'La hora es requerida',
                'time.date_format' => 'La hora ingresada no tiene un formato válido',
                'licensePlate.required' => 'La placa del vehículo es requerida',
                'licensePlate.max' => 'La placa del vehículo debe tener un máximo de 30 caracteres',
                'trailerNumber.max' => 'El número de trailer debe tener un máximo de 30 caracteres',
                'referralNumber.required_if' => 'El número de remisión es requerido',
                'referralNumber.unique' => 'Ya existe '.$type.' con el número de remisión "'.$this->data['referralNumber'].'"',
                'referralNumber.max' => 'El número de remisión debe tener un máximo de 30 caracteres',
                'receiptNumber.required_if' => 'El número de recibo es requerido',
                'receiptNumber.unique' => 'Ya existe '.$type.' con el número de recibo "'.$this->data['receiptNumber'].'"',
                'receiptNumber.max' => 'El número de recibo debe tener un máximo de 30 caracteres',
                'originYard.required_if' => 'El patio de despacho es requerido',
                'originYard.exists' => 'El patio de despacho ingresado no existe',
                'originYard.not_in' => 'El patio de despacho debe ser diferente al patio de destino',
                'destinyYard.required_if' => 'El patio de destino es requerido',
                'destinyYard.exists' => 'El patio de destino ingresado no existe',
                'destinyYard.not_in' => 'El patio de destino debe ser diferente al patio de despacho',
                'customer.required_if' => 'El cliente es requerido',
                'customer.exists' => 'El cliente seleccionado no existe',
                'supplier.required_if' => 'El proveedor es requerido',
                'supplier.exists' => 'El proveedor seleccionado no existe',
                'conveyorCompany.required_if' => 'La empresa transportadora es requerida',
                'conveyorCompany.exists' => 'La empresa transportadora seleccionada no existe',
                'driverName.required' => 'El nombre del conductor es requerido',
                'driverName.max' => 'El nombre del conductor debe tener un máximo de 100 caracteres',
                'driverDocument.required' => 'El documento del conductor es requerido',
                'driverDocument.max' => 'El documento del conductor debe tener un máximo de 20 caracteres',
                'material.required' => 'El material es requerido',
                'material.exists' => 'El material seleccionado no existe',
                'ashPercentaje.numeric' => 'El porcentaje de cenizas debe ser de tipo numérico',
                'ashPercentaje.gte' => 'El porcentaje de cenizas no debe ser negativo',
                'ashPercentaje.lte' => 'El porcentaje de cenizas debe ser menor a 100',
                'grossWeight.required' => 'El peso bruto es requerido',
                'grossWeight.numeric' => 'El peso bruto debe ser de tipo numérico',
                'grossWeight.gt' => 'El peso bruto debe ser mayor a 0',
                'grossWeight.lt' => 'El peso bruto debe ser menor a 100,000,000',
                'tareWeight.required' => 'El peso tara es requerido',
                'tareWeight.numeric' => 'El peso tara debe ser de tipo numérico',
                'tareWeight.gt' => 'El peso tara debe ser mayor a 0',
                'tareWeight.lt' => 'El peso tara debe ser menor a 100,000,000',
                'netWeight.required' => 'El peso neto es requerido',
                'netWeight.numeric' => 'El peso neto debe ser de tipo numérico',
                'netWeight.gt' => 'El peso neto debe ser mayor a 0',
                'netWeight.lt' => 'El peso neto debe ser menor a 100,000,000',
                'seals.max' => 'Los precintos deben tener un máximo de 200 caracteres',
                'observation.max' => 'La observacion debe tener un máximo de 600 caracteres',
                'roundTrip.required_if' => 'Debe indicar si el movimiento corresponde a un viaje redondo o no',
                'roundTrip.boolean' => 'Debe usar alguno de los siguientes valores para indicar si el movimiento corresponde a un viaje redondo o no (true, false, 0, 1, "0" o "1")'
            ];
        }
    }
?>