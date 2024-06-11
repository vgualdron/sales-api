<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    public $table = "settlements";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'type', 
        'consecutive',
        'third',
        'date',
        'subtotal_amount',
        'subtotal_settlement',
        'unit_royalties',
        'royalties',
        'retentions_percentage',
        'retentions',
        'total_settle',
        'observation',
        'invoice',
        'invoice_date',
        'internal_document',
        'start_date',
        'final_date',
        'created_at',
        'updated_at'
    ];
}
