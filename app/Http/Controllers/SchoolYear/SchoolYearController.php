<?php

namespace App\Http\Controllers\SchoolYear;

use App\Http\Controllers\ApiController;
use App\Models\Course;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchoolYearController extends ApiController
{
    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(
            'auth:api'
        );
    }


    public function getCurretYear(Request $request){
        // Usar caché para mejorar el rendimiento
        $schoolYear = cache()->remember('active_school_year', 3600, function () {
            return SchoolYear::where('active', true)->first();
        });
        
        return $this->showOne($schoolYear);
    }

    public function index(Request $request)
    {
        $schoolYears = SchoolYear::query()->orderBy('id', 'DESC');
        $collections = $request->input('all') == 1
        ? $this->showList($schoolYears->get()):
         $this->showAll($schoolYears);
         return $collections;

    }

    public function store(Request $request)
    {

        $schoolYear = SchoolYear::latest()->first();

        if($schoolYear->year <=  date("Y") && !SchoolYear::where('year', date("Y") )->exists()){

            SchoolYear::query()->update(['active' => false]);

            $schoolYear = SchoolYear::create([
                'year' => date("Y"),
                'active' => true
            ]);


            $this->createYearCurrentWithCoursesEnabled();
            
            // Limpiar caché del año escolar activo
            cache()->forget('active_school_year');

            return $this->showOne($schoolYear);
        }else{
            return $this->errorResponse("No se puede crear nuevo ciclo lectivo por el año en curso no ha finalizado", 404);
        }
    }


    public function update(Request $request,SchoolYear $schoolYear)
    {

        $rules = [
            'year' => 'required|max:4|min:4',
            'active' => 'required'
        ];

        $this->validate($request, $rules);

        $schoolYear->active  = true;
        $schoolYear->save();

        SchoolYear::where('id', '<>', $schoolYear->id)->update(['active' => false]);

        $this->updateStatusCourseDependOnYearActive();
        
        // Limpiar caché del año escolar activo
        cache()->forget('active_school_year');

        return $this->showOne($schoolYear);
    }

    public function createYearCurrentWithCoursesEnabled()
    {
        //toma el ciclo lectivo activo
        $schoolYear = SchoolYear::where('active', true)->first();

        if (Course::where('school_year_id', $schoolYear->id)->count() == 0) {
            // Solo toma cursos del ciclo lectivo anterior para evitar duplicados
            $previousSchoolYear = SchoolYear::where('id', '<>', $schoolYear->id)
                ->orderBy('id', 'desc')
                ->first();

            $courses = $previousSchoolYear
                ? Course::where('school_year_id', $previousSchoolYear->id)->get()
                : collect();

            Course::query()->update(['status' => false]);

            $newCourses = $courses->map(function ($record) use ($schoolYear) {
                return [
                    'nombre'        => $record->nombre,
                    'id_profesor'   => $record->id_profesor,
                    'id_nivel'      => $record->id_nivel,
                    'school_year_id'=> $schoolYear->id,
                    'status'        => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            })->toArray();

            if (!empty($newCourses)) {
                Course::insert($newCourses);
            }

            return $this->successResponse("Actualizacion de cursos al ciclo lectivo actual terminada", 200);
        }
        return $this->errorResponse("Hubo una actualizacion reciente con el año lectivo actual", 404);
    }

    /**
     * SQL compartido que identifica los candidatos a borrar.
     * Coincide exactamente con el script SQL de referencia.
     */
    private function candidatesSql()
    {
        return "
            SELECT c.id
            FROM cursos c
            JOIN (
                SELECT nombre
                FROM cursos
                WHERE deleted_at IS NULL
                GROUP BY nombre
                HAVING COUNT(DISTINCT school_year_id) > 1
            ) repetidos ON repetidos.nombre = c.nombre
            WHERE c.deleted_at IS NULL
              AND NOT EXISTS (
                  SELECT 1 FROM alumnos_cursos ac WHERE ac.id_curso = c.id
              )
        ";
    }

    /**
     * PASO 1 — Preview: muestra exactamente qué cursos duplicados se borrarían.
     * Usa el mismo SQL del script de referencia para garantizar consistencia.
     */
    public function previewDuplicateCourses()
    {
        $rows = DB::select("
            WITH cursos_candidatos AS (
                " . $this->candidatesSql() . "
            )
            SELECT
                c.id                AS curso_id,
                c.nombre            AS curso_nombre,
                sy.year             AS anio_academico,
                n.nombre            AS nivel,
                (SELECT COUNT(*) FROM fechas_curso  fc WHERE fc.id_curso = c.id) AS fechas_asistencia,
                (SELECT COUNT(*) FROM evaluaciones  ev WHERE ev.id_curso = c.id) AS evaluaciones
            FROM cursos c
            JOIN school_years sy ON sy.id = c.school_year_id
            LEFT JOIN niveles n   ON n.id  = c.id_nivel
            WHERE c.id IN (SELECT id FROM cursos_candidatos)
            ORDER BY c.nombre, sy.year
        ");

        return response()->json([
            'total'   => count($rows),
            'message' => 'Revisá este listado antes de ejecutar el borrado.',
            'data'    => $rows,
        ]);
    }

    /**
     * PASO 2 — Borrado de emergencia dentro de una transacción.
     * Elimina en orden para respetar FK:
     *   asistencias → fechas_curso → evaluaciones_alumnos → evaluaciones → cursos
     */
    public function deleteDuplicateCourses()
    {
        $candidateIds = collect(DB::select($this->candidatesSql()))->pluck('id');

        if ($candidateIds->isEmpty()) {
            return $this->errorResponse("No se encontraron cursos duplicados para eliminar", 404);
        }

        DB::transaction(function () use ($candidateIds) {
            // 1) IDs de fechas de asistencia asociadas
            $fechaIds = DB::table('fechas_curso')
                ->whereIn('id_curso', $candidateIds)
                ->pluck('id');

            // 2) Registros de asistencia de esas fechas
            DB::table('asistencias')
                ->whereIn('id_fechas_curso', $fechaIds)
                ->delete();

            // 3) Fechas de asistencia
            DB::table('fechas_curso')
                ->whereIn('id_curso', $candidateIds)
                ->delete();

            // 4) IDs de evaluaciones asociadas
            $evaluacionIds = DB::table('evaluaciones')
                ->whereIn('id_curso', $candidateIds)
                ->pluck('id');

            // 5) Notas de evaluaciones
            DB::table('evaluaciones_alumnos')
                ->whereIn('id_evaluacion', $evaluacionIds)
                ->delete();

            // 6) Evaluaciones
            DB::table('evaluaciones')
                ->whereIn('id_curso', $candidateIds)
                ->delete();

            // 7) Cursos (hard delete, bypassea soft deletes)
            DB::table('cursos')
                ->whereIn('id', $candidateIds)
                ->delete();
        });

        return $this->successResponse(
            "Se eliminaron {$candidateIds->count()} cursos duplicados correctamente",
            200
        );
    }

    public function updateStatusCourseDependOnYearActive(){
        $schoolYear = SchoolYear::where('active', true)->first();
        Course::where('school_year_id', '<>', $schoolYear->id)->update(['status' => false]);
        Course::where('school_year_id', $schoolYear->id)->update(['status' => true]);
        return $this->successResponse("Actualizacion de cursos al ciclo lectivo actual terminada", 200);
    }



}
