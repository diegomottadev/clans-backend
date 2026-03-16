<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Month extends Model
{
    use HasFactory;
    // Ciclo lectivo: marzo a diciembre
    const MONTHS = [
        ["id"=>3,"name"=>"Marzo"],
        ["id"=>4,"name"=>"Abril"],
        ["id"=>5,"name"=>"Mayo"],
        ["id"=>6,"name"=>"Junio"],
        ["id"=>7,"name"=>"Julio"],
        ["id"=>8,"name"=>"Agosto"],
        ["id"=>9,"name"=>"Septiembre"],
        ["id"=>10,"name"=>"Octubre"],
        ["id"=>11,"name"=>"Noviembre"],
        ["id"=>12,"name"=>"Diciembre"],
        ["id"=>13,"name"=>"El curso ha sido pagado en su totalidad"],
    ];
}
