<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssistanceSeeder2026 extends Seeder
{
    /**
     * Crea asistencias para el año 2026.
     *
     * Estructura:
     *   - fechas_curso : registro de día de clase (id, fecha, id_curso)
     *   - asistencias  : asistencia por alumno (id_fechas_curso, id_alumno, id_tipos_asistencia)
     *   - alumnos_cursos: inscripción del alumno al curso
     *
     * Tipos de asistencia: 1=Presente  2=Ausente  3=Demora  4=Se Retiró
     */
    public function run()
    {
        // -------------------------------------------------------
        // Cursos 2026 seleccionados
        // -------------------------------------------------------
        // 1529 - 4th Elem AFTERNOON       (nivel 54)
        // 1530 - JUNIOR A                 (nivel 63)
        // 1531 - 3RD ELEM AFTERNOON       (nivel 5)
        // 1532 - 4TH TEENS B              (nivel 11)
        // 1534 - INGLES 5 TEENS           (nivel 12)

        // -------------------------------------------------------
        // Alumnos que usaremos (existentes en la BD)
        // -------------------------------------------------------
        $alumnosCurso1529 = [1972, 1973, 1974, 1975, 1976];
        $alumnosCurso1530 = [1977, 1978, 1979, 1980, 1981];
        $alumnosCurso1531 = [1982, 1983, 1984, 1985, 1986];
        $alumnosCurso1532 = [1987, 1988, 1989];
        $alumnosCurso1534 = [1972, 1977, 1982, 1987];

        // -------------------------------------------------------
        // 1. Inscribir alumnos en los cursos 2026 (alumnos_cursos)
        //    Solo si no existe ya la inscripción
        // -------------------------------------------------------
        $inscripciones = [
            1529 => $alumnosCurso1529,
            1530 => $alumnosCurso1530,
            1531 => $alumnosCurso1531,
            1532 => $alumnosCurso1532,
            1534 => $alumnosCurso1534,
        ];

        $nivelPorCurso = [
            1529 => 54,
            1530 => 63,
            1531 => 5,
            1532 => 11,
            1534 => 12,
        ];

        foreach ($inscripciones as $cursoId => $alumnos) {
            foreach ($alumnos as $alumnoId) {
                $existe = DB::table('alumnos_cursos')
                    ->where('id_curso', $cursoId)
                    ->where('id_alumno', $alumnoId)
                    ->exists();
                if (!$existe) {
                    DB::table('alumnos_cursos')->insert([
                        'id_alumno'    => $alumnoId,
                        'id_curso'     => $cursoId,
                        'fecha'        => '2026-03-01',
                        'anio_lectivo' => 2026,
                        'estado'       => null,
                        'id_nivel'     => $nivelPorCurso[$cursoId],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }
        }

        // -------------------------------------------------------
        // 2. Fechas de clase + asistencias por alumno
        // -------------------------------------------------------
        $clases = [
            // Curso 1529 - 4th Elem AFTERNOON
            [
                'fecha'    => '2026-03-03',
                'id_curso' => 1529,
                'alumnos'  => [
                    [1972, 1], [1973, 1], [1974, 2], [1975, 1], [1976, 3],
                ],
            ],
            [
                'fecha'    => '2026-03-10',
                'id_curso' => 1529,
                'alumnos'  => [
                    [1972, 1], [1973, 2], [1974, 1], [1975, 1], [1976, 1],
                ],
            ],
            [
                'fecha'    => '2026-03-17',
                'id_curso' => 1529,
                'alumnos'  => [
                    [1972, 1], [1973, 1], [1974, 1], [1975, 4], [1976, 1],
                ],
            ],

            // Curso 1530 - JUNIOR A
            [
                'fecha'    => '2026-03-04',
                'id_curso' => 1530,
                'alumnos'  => [
                    [1977, 1], [1978, 1], [1979, 2], [1980, 1], [1981, 1],
                ],
            ],
            [
                'fecha'    => '2026-03-11',
                'id_curso' => 1530,
                'alumnos'  => [
                    [1977, 1], [1978, 3], [1979, 1], [1980, 2], [1981, 1],
                ],
            ],

            // Curso 1531 - 3RD ELEM AFTERNOON
            [
                'fecha'    => '2026-03-05',
                'id_curso' => 1531,
                'alumnos'  => [
                    [1982, 1], [1983, 1], [1984, 2], [1985, 1], [1986, 1],
                ],
            ],
            [
                'fecha'    => '2026-03-12',
                'id_curso' => 1531,
                'alumnos'  => [
                    [1982, 2], [1983, 1], [1984, 1], [1985, 1], [1986, 4],
                ],
            ],

            // Curso 1532 - 4TH TEENS B
            [
                'fecha'    => '2026-03-06',
                'id_curso' => 1532,
                'alumnos'  => [
                    [1987, 1], [1988, 1], [1989, 2],
                ],
            ],
            [
                'fecha'    => '2026-03-13',
                'id_curso' => 1532,
                'alumnos'  => [
                    [1987, 3], [1988, 1], [1989, 1],
                ],
            ],

            // Curso 1534 - INGLES 5 TEENS
            [
                'fecha'    => '2026-03-07',
                'id_curso' => 1534,
                'alumnos'  => [
                    [1972, 1], [1977, 1], [1982, 2], [1987, 1],
                ],
            ],
            [
                'fecha'    => '2026-03-14',
                'id_curso' => 1534,
                'alumnos'  => [
                    [1972, 1], [1977, 2], [1982, 1], [1987, 1],
                ],
            ],
            [
                'fecha'    => '2026-03-21',
                'id_curso' => 1534,
                'alumnos'  => [
                    [1972, 1], [1977, 1], [1982, 1], [1987, 4],
                ],
            ],
        ];

        foreach ($clases as $clase) {
            // Insertar la fecha de clase
            $fechaCursoId = DB::table('fechas_curso')->insertGetId([
                'fecha'      => $clase['fecha'],
                'id_curso'   => $clase['id_curso'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insertar asistencia de cada alumno
            foreach ($clase['alumnos'] as [$alumnoId, $tipoId]) {
                DB::table('asistencias')->insert([
                    'id_fechas_curso'   => $fechaCursoId,
                    'id_alumno'         => $alumnoId,
                    'id_tipos_asistencia' => $tipoId,
                ]);
            }
        }
    }
}
