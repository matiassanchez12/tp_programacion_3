<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'encuesta';

    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'codigo_mesa', 'punt_mesa', 'punt_restaurante', 'punt_mozo', 'punt_cocinero', 'comentarios'
    ];

    public static function crearEncuesta($codigo_mesa, $punt_mesa, $punt_restaurante, $punt_mozo, $punt_cocinero, $comentarios)
    {
        $encuesta = new Encuesta([
            'codigo_mesa' => $codigo_mesa,
            'punt_mesa' => $punt_mesa,
            'punt_restaurante' => $punt_restaurante, 
            'punt_mozo' => $punt_mozo, 
            'punt_cocinero' => $punt_cocinero,
            'comentarios' => $comentarios,
        ]);

        $encuesta->save();
    }
}
