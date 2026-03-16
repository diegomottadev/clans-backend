<?php

namespace Database\Seeders;

use App\Models\Concept;
use App\Models\Invoice;
use App\Models\StudentCourse;
use Illuminate\Database\Seeder;

class NotasConceptosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Concept::create([
            'nombre' => 'Uno',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Due',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Tre',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Quattro',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Cinque',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Sei',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Sette',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Otto',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Nove',
            'id_idioma' =>4
        ]);
        Concept::create([
            'nombre' => 'Dieci',
            'id_idioma' =>4
        ]);
    }
}
