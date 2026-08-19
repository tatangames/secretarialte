<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';
    public $timestamps = false;

    protected $fillable = [
        'id_lugares',
        'nombre',
        'telefono',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'descripcion'
    ];

    public function lugar()
    {
        return $this->belongsTo(Lugar::class, 'id_lugares');
    }
}
