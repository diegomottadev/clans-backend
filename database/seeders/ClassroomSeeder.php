<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassroomSeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $classrooms = [
            ['nombre' => 'Aula 1', 'capacidad' => 20],
            ['nombre' => 'Aula 2', 'capacidad' => 25],
            ['nombre' => 'Aula 3', 'capacidad' => 15],
            ['nombre' => 'Aula 4', 'capacidad' => 30],
            ['nombre' => 'Aula 5', 'capacidad' => 20],
            ['nombre' => 'Lab de Idiomas', 'capacidad' => 18],
            ['nombre' => 'Sala de Conferencias', 'capacidad' => 40],
            ['nombre' => 'Aula Virtual', 'capacidad' => null],
        ];

        foreach ($classrooms as $c) {
            DB::table('aulas')->insert(array_merge($c, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
