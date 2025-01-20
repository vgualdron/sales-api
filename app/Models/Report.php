<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $connection = 'mysql';
    public $table = "reports";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'sql',
        'order',
        'permission',
        'background',
        'color',
    ];
}
