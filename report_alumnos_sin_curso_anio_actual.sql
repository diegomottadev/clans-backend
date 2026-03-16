-- =============================================================================
-- Alumnos sin cursos en el año lectivo actual
-- PostgreSQL - Ejecutar en la base del proyecto clans
-- =============================================================================

-- 1) Año lectivo actual (el que tiene active = true)
WITH anio_actual AS (
    SELECT id, year
    FROM school_years
    WHERE active = true
    LIMIT 1
),

-- 2) Alumnos que NO tienen ningún curso en ese año
alumnos_sin_curso AS (
    SELECT a.id,
           a.nombre,
           a.apellido,
           a."nombreCompleto",
           a.dni,
           a.activo
    FROM alumnos a
    WHERE NOT EXISTS (
        SELECT 1
        FROM alumnos_cursos ac
        INNER JOIN cursos c ON c.id = ac.id_curso
        INNER JOIN anio_actual ay ON ay.id = c.school_year_id
        WHERE ac.id_alumno = a.id
    )
      -- Opcional: excluir alumnos borrados (soft delete) si la tabla tiene deleted_at
      -- AND a.deleted_at IS NULL
)

-- Total de alumnos sin curso en el año actual
SELECT COUNT(*) AS total_alumnos_sin_curso
FROM alumnos_sin_curso;

-- =============================================================================
-- Descomentar el siguiente bloque para ver el detalle (lista de alumnos)
-- =============================================================================
/*
WITH anio_actual AS (
    SELECT id, year FROM school_years WHERE active = true LIMIT 1
)
SELECT a.id,
       a."nombreCompleto",
       a.dni,
       a.activo,
       (SELECT year FROM anio_actual) AS anio_lectivo_actual
FROM alumnos a
WHERE NOT EXISTS (
    SELECT 1
    FROM alumnos_cursos ac
    INNER JOIN cursos c ON c.id = ac.id_curso
    INNER JOIN anio_actual ay ON ay.id = c.school_year_id
    WHERE ac.id_alumno = a.id
)
ORDER BY a."nombreCompleto";
*/
