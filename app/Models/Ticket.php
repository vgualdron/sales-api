<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $table = "tickets";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type',
        'user',
        'origin_yard',
        'destiny_yard',
        'supplier',
        'customer',
        'material',
        'ash_percentage',
        'receipt_number',
        'referral_number',
        'date',
        'time',
        'license_plate',
        'trailer_number',
        'driver_document',
        'driver_name',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'conveyor_company',
        'observation',
        'seals',
        'round_trip',
        'local_created_at',
        'freight_settlement',
        'material_settlement',
        'freight_settlement_retention_percentage',
        'material_settlement_retention_percentage',
        'material_settlement_royalties',
        'freight_settlement_unit_value',
        'material_settlement_unit_value',
        'freight_settlement_net_value',
        'material_settlement_net_value',
        'material_settle_receipt_weight',
        'freight_settle_receipt_weight',
        'freight_weight_settled',
        'material_weight_settled',
        'ticketmovid',
        'consecutive',
        'created_at',
        'updated_at'
    ];
}
