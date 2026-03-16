<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Languaje;
use App\Models\Level;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\Teacher;
use App\Models\TypeAssistance;
use App\Models\TypeEvaluation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TeacherUserSeeder extends Seeder
{
    public function run()
    {
        // 1. School year
        $schoolYear = SchoolYear::firstOrCreate(
            ['year' => 2026],
            ['active' => true]
        );

        // 2. Language
        $language = Languaje::firstOrCreate(['nombre' => 'Inglés']);

        // 3. Level
        $level = Level::firstOrCreate(
            ['nombre' => 'Nivel 1', 'id_idioma' => $language->id],
            ['cuota' => 5000, 'estado' => true, 'mes_desde' => 3, 'mes_hasta' => 12]
        );

        $level2 = Level::firstOrCreate(
            ['nombre' => 'Nivel 2', 'id_idioma' => $language->id],
            ['cuota' => 5500, 'estado' => true, 'mes_desde' => 3, 'mes_hasta' => 12]
        );

        // 4. Teacher
        $teacher = Teacher::firstOrCreate(
            ['dni' => '30000001'],
            [
                'apellido' => 'García',
                'nombre' => 'María',
                'tel_part' => '3514000001',
                'cel_part' => '3514000001',
                'email' => 'docente@clans.com',
                'domicilio' => 'Córdoba',
                'nombreCompleto' => 'García, María',
            ]
        );

        // 5. User with teacher role
        User::updateOrCreate(
            ['email' => 'docente@clans.com'],
            [
                'name' => 'María García',
                'role' => 'user',
                'email_verified_at' => Carbon::now(),
                'password' => bcrypt('docente123'),
                'id_profesor' => $teacher->id,
            ]
        );

        // 6. Classroom
        $classroom = Classroom::firstOrCreate(
            ['nombre' => 'Aula 1'],
            ['capacidad' => 20]
        );

        // 7. Courses assigned to the teacher
        $course1 = Course::firstOrCreate(
            ['nombre' => 'Inglés Nivel 1 - Turno Mañana', 'school_year_id' => $schoolYear->id, 'id_profesor' => $teacher->id],
            ['id_nivel' => $level->id, 'status' => true]
        );

        $course2 = Course::firstOrCreate(
            ['nombre' => 'Inglés Nivel 2 - Turno Tarde', 'school_year_id' => $schoolYear->id, 'id_profesor' => $teacher->id],
            ['id_nivel' => $level2->id, 'status' => true]
        );

        // 8. Students
        $studentsData = [
            ['apellido' => 'López', 'nombre' => 'Juan', 'dni' => '50000001'],
            ['apellido' => 'Martínez', 'nombre' => 'Ana', 'dni' => '50000002'],
            ['apellido' => 'Rodríguez', 'nombre' => 'Carlos', 'dni' => '50000003'],
            ['apellido' => 'Fernández', 'nombre' => 'Lucía', 'dni' => '50000004'],
            ['apellido' => 'Pérez', 'nombre' => 'Mateo', 'dni' => '50000005'],
            ['apellido' => 'González', 'nombre' => 'Sofía', 'dni' => '50000006'],
            ['apellido' => 'Díaz', 'nombre' => 'Tomás', 'dni' => '50000007'],
            ['apellido' => 'Torres', 'nombre' => 'Valentina', 'dni' => '50000008'],
        ];

        $students = [];
        foreach ($studentsData as $sd) {
            $students[] = Student::firstOrCreate(
                ['dni' => $sd['dni']],
                [
                    'apellido' => $sd['apellido'],
                    'nombre' => $sd['nombre'],
                    'activo' => true,
                    'nombreCompleto' => $sd['apellido'] . ', ' . $sd['nombre'],
                ]
            );
        }

        // 9. Enroll students in courses
        $today = Carbon::now();
        foreach ($students as $i => $student) {
            // First 5 students in course 1, last 5 in course 2 (some in both)
            $assignCourses = $i < 5 ? [$course1] : [$course2];
            if ($i >= 3 && $i <= 5) {
                $assignCourses = [$course1, $course2]; // students 4-6 in both
            }

            foreach ($assignCourses as $course) {
                StudentCourse::firstOrCreate(
                    ['id_alumno' => $student->id, 'id_curso' => $course->id],
                    [
                        'fecha' => $today,
                        'anio_lectivo' => 2026,
                        'estado' => true,
                        'id_nivel' => $course->id_nivel,
                        'id_idioma' => $language->id,
                        'importe' => $course->id === $course1->id ? 5000 : 5500,
                        'pagado' => false,
                    ]
                );
            }
        }

        // 10. Class schedules for this week and next
        $startOfWeek = Carbon::now()->startOfWeek();
        $schedules = [
            // Course 1: Mon & Wed 9:00-10:30
            ['course' => $course1, 'dayOffset' => 0, 'start' => '09:00', 'end' => '10:30'],
            ['course' => $course1, 'dayOffset' => 2, 'start' => '09:00', 'end' => '10:30'],
            // Course 2: Tue & Thu 14:00-15:30
            ['course' => $course2, 'dayOffset' => 1, 'start' => '14:00', 'end' => '15:30'],
            ['course' => $course2, 'dayOffset' => 3, 'start' => '14:00', 'end' => '15:30'],
        ];

        foreach ([0, 7] as $weekOffset) { // this week and next
            foreach ($schedules as $s) {
                $date = $startOfWeek->copy()->addDays($s['dayOffset'] + $weekOffset);
                ClassSchedule::firstOrCreate(
                    [
                        'id_curso' => $s['course']->id,
                        'fecha' => $date->format('Y-m-d'),
                        'hora_inicio' => $s['start'],
                    ],
                    [
                        'id_profesor' => $teacher->id,
                        'id_aula' => $classroom->id,
                        'hora_fin' => $s['end'],
                    ]
                );
            }
        }

        // 11. Attendance types (if not exist)
        TypeAssistance::firstOrCreate(['nombre' => 'Presente']);
        TypeAssistance::firstOrCreate(['nombre' => 'Ausente']);
        TypeAssistance::firstOrCreate(['nombre' => 'Tardanza']);

        // 12. Evaluation types (if not exist)
        TypeEvaluation::firstOrCreate(['nombre' => 'Examen Parcial']);
        TypeEvaluation::firstOrCreate(['nombre' => 'Examen Final']);
        TypeEvaluation::firstOrCreate(['nombre' => 'Trabajo Práctico']);

        echo "\n=== TEACHER PWA SEEDER COMPLETE ===\n";
        echo "Login: docente@clans.com / docente123\n";
        echo "Courses: {$course1->nombre}, {$course2->nombre}\n";
        echo "Students: " . count($students) . " enrolled\n";
        echo "Classes scheduled for this and next week\n";
        echo "===================================\n";
    }
}
