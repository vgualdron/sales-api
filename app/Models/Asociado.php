<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asociado extends Model
{
    use HasFactory;

    protected $connection = 'mysql_secondary';
    protected $table = 'asociados';

    protected $fillable = [
        'id',
        'fecha_afiliacion',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'tipo_documento',
        'cedula',
        'fecha_expedicion',
        'dpto_expedicion',
        'lugar_expedicion',
        'fecha_nacimiento',
        'edad',
        'dpto_nacimiento',
        'lugar_nacimiento',
        'nacionalidad',
        'cedula_representante',
        'nombre_representante',
        'edad_representante',
        'genero',
        'estado_civil',
        'personas_adultos',
        'personas_menores',
        'cabeza_familia',
        'tipo_vivienda',
        'estrato',
        'dpto',
        'ciudad',
        'direccion',
        'telefono',
        'celular',
        'email',
        'nivel_educativo',
        'profesion',
        'idiomas',
        'hobbies',
        'autoriza_residencia',
        'autoriza_trabajo',
        'autoriza_familiar',
        'autoriza_email',
        'autoriza_telefono',
        'autoriza_datos',
        'estado',
    ];

    /*
    // Relación uno a uno con el modelo Economica
    public function economicas()
    {
        return $this->hasOne(Economica::class);
    }

    // Relación uno a uno con el modelo Activo
    public function activos()
    {
        return $this->hasOne(Activo::class);
    }

    // Relación uno a uno con el modelo Activo
    public function conocimientos()
    {
        return $this->hasOne(Conocimiento::class);
    }

    // Relación uno a uno con el modelo Activo
    public function referencias()
    {
        return $this->hasOne(Referencia::class);
    }

    // Relación uno a muchos con el modelo Aportes
    public function aportes()
    {
        return $this->hasMany(AsociadoAporte::class);
    }

    // Relación uno a muchos con el modelo Municipios (ciudad de residencia)
    public function municipio_residencia()
    {
        return $this->belongsTo(Municipio::class, 'ciudad', 'id'); // 'ciudad' es la clave foránea en asociados, y 'id' es la clave primaria en municipios
    }

    // Relación uno a muchos con el modelo Departamentos (departamento de residencia)
    public function departamento_residencia()
    {
        return $this->belongsTo(Departamento::class, 'dpto', 'id'); // 'dpto' es la clave foránea en asociados, y 'id' es la clave primaria en departamentos
    }

    // Relación uno a muchos con el modelo Municipios (lugar de expedición de cédula)
    public function municipio_expedicion()
    {
        return $this->belongsTo(Municipio::class, 'lugar_expedicion', 'id'); // 'lugar_expedicion' es la clave foránea en asociados, y 'id' es la clave primaria en municipios
    }

    // Relación uno a muchos con el modelo Departamentos (departamento de expedición de cédula)
    public function departamento_expedicion()
    {
        return $this->belongsTo(Departamento::class, 'dpto_expedicion', 'id'); // 'dpto_expedicion' es la clave foránea en asociados, y 'id' es la clave primaria en departamentos
    }

    // Relación uno a muchos con el modelo Municipios (lugar de expedición de cédula)
    public function municipio_nacimiento()
    {
        return $this->belongsTo(Municipio::class, 'lugar_nacimiento', 'id'); // 'lugar_nacimiento' es la clave foránea en asociados, y 'id' es la clave primaria en municipios
    }

    // Relación uno a muchos con el modelo Departamentos (departamento de expedición de cédula)
    public function departamento_nacimiento()
    {
        return $this->belongsTo(Departamento::class, 'dpto_nacimiento', 'id'); // 'dpto_nacimiento' es la clave foránea en asociados, y 'id' es la clave primaria en departamentos
    } */
}
