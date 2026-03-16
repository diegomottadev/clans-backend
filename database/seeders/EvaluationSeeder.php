<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationSeeder extends Seeder
{
    public function run()
    {
        // Evaluaciones con alumnos reales de la BD
        // Cursos: 4,6,8,9,12,18,26,27 | Tipos: 1-Final,2-Practico,3-Oral,4-Parcial,5-ENTREGA ABOOK

        $evaluaciones = [
            [
                'nombre'             => 'Parcial 1 - Primer Cuatrimestre',
                'fecha'              => '2025-04-10',
                'id_tipo_evaluacion' => 4,
                'id_curso'           => 26,
                'alumnos'            => [
                    ['id' => 182, 'listening' => 8.5, 'vocabulary' => 7.0, 'languajeFocus' => 9.0, 'reading' => 8.0, 'communication' => 7.5, 'writing' => 8.0, 'oralExam' => null, 'observaciones' => 'Buen desempeño general'],
                    ['id' => 181, 'listening' => 6.0, 'vocabulary' => 5.5, 'languajeFocus' => 7.0, 'reading' => 6.5, 'communication' => 6.0, 'writing' => 5.0, 'oralExam' => null, 'observaciones' => 'Debe mejorar escritura'],
                    ['id' => 180, 'listening' => 9.0, 'vocabulary' => 8.5, 'languajeFocus' => 9.5, 'reading' => 9.0, 'communication' => 8.0, 'writing' => 9.0, 'oralExam' => null, 'observaciones' => 'Excelente'],
                    ['id' => 151, 'listening' => 7.0, 'vocabulary' => 7.5, 'languajeFocus' => 6.5, 'reading' => 7.0, 'communication' => 8.0, 'writing' => 7.0, 'oralExam' => null, 'observaciones' => ''],
                ],
            ],
            [
                'nombre'             => 'Final Oral - Junio',
                'fecha'              => '2025-06-20',
                'id_tipo_evaluacion' => 3,
                'id_curso'           => 26,
                'alumnos'            => [
                    ['id' => 182, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => 9.0, 'writing' => null, 'oralExam' => 8.5, 'observaciones' => 'Muy buena fluidez'],
                    ['id' => 181, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => 6.5, 'writing' => null, 'oralExam' => 6.0, 'observaciones' => 'Nervioso, mejorar pronunciación'],
                    ['id' => 180, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => 9.5, 'writing' => null, 'oralExam' => 9.0, 'observaciones' => 'Sobresaliente'],
                    ['id' => 151, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => 7.5, 'writing' => null, 'oralExam' => 7.0, 'observaciones' => ''],
                ],
            ],
            [
                'nombre'             => 'Práctico 1 - Vocabulario',
                'fecha'              => '2025-03-15',
                'id_tipo_evaluacion' => 2,
                'id_curso'           => 18,
                'alumnos'            => [
                    ['id' => 178, 'listening' => null, 'vocabulary' => 8.0, 'languajeFocus' => 7.5, 'reading' => null, 'communication' => null, 'writing' => 8.5, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 177, 'listening' => null, 'vocabulary' => 6.0, 'languajeFocus' => 5.5, 'reading' => null, 'communication' => null, 'writing' => 6.0, 'oralExam' => null, 'observaciones' => 'Recuperatorio pendiente'],
                    ['id' => 175, 'listening' => null, 'vocabulary' => 9.0, 'languajeFocus' => 8.5, 'reading' => null, 'communication' => null, 'writing' => 9.5, 'oralExam' => null, 'observaciones' => 'Muy bien'],
                    ['id' => 59,  'listening' => null, 'vocabulary' => 7.0, 'languajeFocus' => 7.0, 'reading' => null, 'communication' => null, 'writing' => 7.5, 'oralExam' => null, 'observaciones' => ''],
                ],
            ],
            [
                'nombre'             => 'Parcial 2 - Segundo Cuatrimestre',
                'fecha'              => '2025-08-22',
                'id_tipo_evaluacion' => 4,
                'id_curso'           => 18,
                'alumnos'            => [
                    ['id' => 178, 'listening' => 7.5, 'vocabulary' => 8.0, 'languajeFocus' => 7.0, 'reading' => 8.5, 'communication' => 7.0, 'writing' => 8.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 177, 'listening' => 5.0, 'vocabulary' => 5.5, 'languajeFocus' => 6.0, 'reading' => 5.0, 'communication' => 5.5, 'writing' => 5.0, 'oralExam' => null, 'observaciones' => 'Bajo rendimiento'],
                    ['id' => 175, 'listening' => 9.5, 'vocabulary' => 9.0, 'languajeFocus' => 9.0, 'reading' => 9.5, 'communication' => 8.5, 'writing' => 9.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 59,  'listening' => 7.0, 'vocabulary' => 6.5, 'languajeFocus' => 7.5, 'reading' => 7.0, 'communication' => 7.0, 'writing' => 6.5, 'oralExam' => null, 'observaciones' => ''],
                ],
            ],
            [
                'nombre'             => 'Final Escrito - Diciembre',
                'fecha'              => '2025-12-05',
                'id_tipo_evaluacion' => 1,
                'id_curso'           => 9,
                'alumnos'            => [
                    ['id' => 159, 'listening' => 8.0, 'vocabulary' => 8.5, 'languajeFocus' => 7.5, 'reading' => 9.0, 'communication' => 8.0, 'writing' => 7.5, 'oralExam' => null, 'observaciones' => 'Aprobado'],
                    ['id' => 157, 'listening' => 6.5, 'vocabulary' => 7.0, 'languajeFocus' => 6.0, 'reading' => 7.0, 'communication' => 6.5, 'writing' => 6.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 156, 'listening' => 4.0, 'vocabulary' => 4.5, 'languajeFocus' => 3.5, 'reading' => 5.0, 'communication' => 4.0, 'writing' => 4.0, 'oralExam' => null, 'observaciones' => 'Desaprobado, recuperatorio'],
                    ['id' => 155, 'listening' => 9.0, 'vocabulary' => 9.5, 'languajeFocus' => 8.5, 'reading' => 9.0, 'communication' => 9.5, 'writing' => 9.0, 'oralExam' => null, 'observaciones' => 'Sobresaliente'],
                    ['id' => 153, 'listening' => 7.0, 'vocabulary' => 7.5, 'languajeFocus' => 7.0, 'reading' => 8.0, 'communication' => 7.0, 'writing' => 7.5, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 152, 'listening' => 6.0, 'vocabulary' => 6.5, 'languajeFocus' => 6.5, 'reading' => 6.0, 'communication' => 6.0, 'writing' => 6.0, 'oralExam' => null, 'observaciones' => ''],
                ],
            ],
            [
                'nombre'             => 'Práctico Reading & Writing',
                'fecha'              => '2025-05-08',
                'id_tipo_evaluacion' => 2,
                'id_curso'           => 9,
                'alumnos'            => [
                    ['id' => 159, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => 8.5, 'communication' => null, 'writing' => 8.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 157, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => 7.0, 'communication' => null, 'writing' => 6.5, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 156, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => 5.5, 'communication' => null, 'writing' => 4.5, 'oralExam' => null, 'observaciones' => 'Necesita refuerzo'],
                    ['id' => 155, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => 9.5, 'communication' => null, 'writing' => 9.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 153, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => 7.5, 'communication' => null, 'writing' => 7.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 152, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => 6.5, 'communication' => null, 'writing' => 6.0, 'oralExam' => null, 'observaciones' => ''],
                ],
            ],
            [
                'nombre'             => 'Entrega Activity Book U1-U3',
                'fecha'              => '2025-04-28',
                'id_tipo_evaluacion' => 5,
                'id_curso'           => 27,
                'alumnos'            => [
                    ['id' => 168, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => 'Entregado completo', 'delivered' => true],
                    ['id' => 50,  'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => 'Entregado incompleto', 'delivered' => true],
                    ['id' => 166, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => 'Pendiente', 'pending' => true],
                    ['id' => 165, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => 'Entregado', 'delivered' => true],
                    ['id' => 164, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => 'Pendiente', 'pending' => true],
                ],
            ],
            [
                'nombre'             => 'Parcial 1 - Grammar & Vocabulary',
                'fecha'              => '2025-04-03',
                'id_tipo_evaluacion' => 4,
                'id_curso'           => 27,
                'alumnos'            => [
                    ['id' => 168, 'listening' => 8.0, 'vocabulary' => 9.0, 'languajeFocus' => 8.5, 'reading' => 7.5, 'communication' => 8.0, 'writing' => 8.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 50,  'listening' => 7.0, 'vocabulary' => 7.5, 'languajeFocus' => 6.5, 'reading' => 7.0, 'communication' => 7.5, 'writing' => 7.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 166, 'listening' => 5.5, 'vocabulary' => 6.0, 'languajeFocus' => 5.0, 'reading' => 5.5, 'communication' => 6.0, 'writing' => 5.0, 'oralExam' => null, 'observaciones' => 'Justo aprobado'],
                    ['id' => 165, 'listening' => 9.5, 'vocabulary' => 9.0, 'languajeFocus' => 9.5, 'reading' => 9.0, 'communication' => 9.5, 'writing' => 9.0, 'oralExam' => null, 'observaciones' => 'Excelente'],
                    ['id' => 164, 'listening' => 6.5, 'vocabulary' => 7.0, 'languajeFocus' => 6.0, 'reading' => 7.0, 'communication' => 6.5, 'writing' => 6.5, 'oralExam' => null, 'observaciones' => ''],
                ],
            ],
            [
                'nombre'             => 'Práctico Listening Comprehension',
                'fecha'              => '2025-07-10',
                'id_tipo_evaluacion' => 2,
                'id_curso'           => 12,
                'alumnos'            => [
                    ['id' => 66,  'listening' => 9.0, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 163, 'listening' => 7.5, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 162, 'listening' => 6.0, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => 'Dificultad con acentos'],
                    ['id' => 160, 'listening' => 8.5, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => null, 'writing' => null, 'oralExam' => null, 'observaciones' => ''],
                ],
            ],
            [
                'nombre'             => 'Final Escrito - Diciembre',
                'fecha'              => '2025-12-12',
                'id_tipo_evaluacion' => 1,
                'id_curso'           => 12,
                'alumnos'            => [
                    ['id' => 66,  'listening' => 8.5, 'vocabulary' => 8.0, 'languajeFocus' => 8.5, 'reading' => 9.0, 'communication' => 8.0, 'writing' => 8.5, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 163, 'listening' => 7.0, 'vocabulary' => 7.5, 'languajeFocus' => 7.0, 'reading' => 7.5, 'communication' => 7.0, 'writing' => 7.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 162, 'listening' => 5.5, 'vocabulary' => 6.0, 'languajeFocus' => 5.0, 'reading' => 6.0, 'communication' => 5.5, 'writing' => 5.5, 'oralExam' => null, 'observaciones' => 'Aprobado por poco'],
                    ['id' => 160, 'listening' => 9.0, 'vocabulary' => 8.5, 'languajeFocus' => 9.0, 'reading' => 9.5, 'communication' => 9.0, 'writing' => 9.0, 'oralExam' => null, 'observaciones' => 'Sobresaliente'],
                ],
            ],
            [
                'nombre'             => 'Oral Exam - Conversación',
                'fecha'              => '2025-09-18',
                'id_tipo_evaluacion' => 3,
                'id_curso'           => 4,
                'alumnos'            => [
                    ['id' => 173, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => 8.5, 'writing' => null, 'oralExam' => 8.0, 'observaciones' => 'Buena pronunciación'],
                    ['id' => 172, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => 7.0, 'writing' => null, 'oralExam' => 6.5, 'observaciones' => ''],
                    ['id' => 171, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => 5.0, 'writing' => null, 'oralExam' => 4.5, 'observaciones' => 'Recuperatorio oral'],
                    ['id' => 169, 'listening' => null, 'vocabulary' => null, 'languajeFocus' => null, 'reading' => null, 'communication' => 9.5, 'writing' => null, 'oralExam' => 9.0, 'observaciones' => 'Excelente fluidez'],
                ],
            ],
            [
                'nombre'             => 'Parcial Integrador - Anual',
                'fecha'              => '2025-10-30',
                'id_tipo_evaluacion' => 4,
                'id_curso'           => 8,
                'alumnos'            => [
                    ['id' => 145, 'listening' => 7.5, 'vocabulary' => 8.0, 'languajeFocus' => 7.0, 'reading' => 8.5, 'communication' => 7.5, 'writing' => 7.0, 'oralExam' => null, 'observaciones' => ''],
                    ['id' => 150, 'listening' => 6.0, 'vocabulary' => 6.5, 'languajeFocus' => 5.5, 'reading' => 7.0, 'communication' => 6.0, 'writing' => 6.0, 'oralExam' => null, 'observaciones' => 'Debe reforzar grammar'],
                ],
            ],
        ];

        foreach ($evaluaciones as $data) {
            $alumnos = $data['alumnos'];
            unset($data['alumnos']);

            $evaluation = Evaluation::create($data);

            foreach ($alumnos as $alumno) {
                DB::table('evaluaciones_alumnos')->insert([
                    'id_evaluacion'  => $evaluation->id,
                    'id_alumno'      => $alumno['id'],
                    'listening'      => $alumno['listening'] ?? null,
                    'vocabulary'     => $alumno['vocabulary'] ?? null,
                    'languajeFocus'  => $alumno['languajeFocus'] ?? null,
                    'reading'        => $alumno['reading'] ?? null,
                    'communication'  => $alumno['communication'] ?? null,
                    'writing'        => $alumno['writing'] ?? null,
                    'oralExam'       => $alumno['oralExam'] ?? null,
                    'observaciones'  => $alumno['observaciones'] ?? '',
                    'pending'        => $alumno['pending'] ?? false,
                    'delivered'      => $alumno['delivered'] ?? false,
                    'assigned'       => $alumno['assigned'] ?? false,
                    'concept'        => null,
                    'id_concepto'    => null,
                ]);
            }
        }
    }
}
