<?php

namespace Tests\Feature\Security\Owasp;

use App\Models\Course;
use App\Models\SchoolYear;
use App\Models\User;

/** Datos de prueba: un docente con cursos propios y un curso de otro docente. */
trait TeacherFixtures
{
    protected function teacher(): User
    {
        return User::where('role', 'user')->whereNotNull('id_profesor')
            ->whereIn('id_profesor', $this->activeCourses()->select('id_profesor'))
            ->orderBy('id')->firstOrFail();
    }

    protected function ownCourse(User $teacher): Course
    {
        return $this->activeCourses()->where('id_profesor', $teacher->id_profesor)->orderBy('id')->firstOrFail();
    }

    /** Cursos habilitados del año lectivo activo (los que usa el docente en la app). */
    protected function activeCourses()
    {
        return Course::where('status', true)
            ->where('school_year_id', SchoolYear::where('active', true)->value('id'));
    }

    protected function foreignCourse(User $teacher): Course
    {
        return Course::where('id_profesor', '!=', $teacher->id_profesor)->orderBy('id')->firstOrFail();
    }
}
