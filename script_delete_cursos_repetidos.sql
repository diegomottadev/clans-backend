 Script completo en 3 pasos:

  -- ============================================================
  -- PASO 1: PREVIEW — Ver exactamente qué se borrará
  --         Ejecutá esto primero y revisá el resultado
  -- ============================================================
  WITH cursos_candidatos AS (
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
  )
  SELECT
      c.id                            AS curso_id,
      c.nombre                        AS curso_nombre,
      sy."year"                       AS anio_academico,
      n.nombre                        AS nivel,
      (SELECT COUNT(*) FROM fechas_curso    fc WHERE fc.id_curso = c.id) AS fechas_asistencia,
      (SELECT COUNT(*) FROM evaluaciones   ev WHERE ev.id_curso = c.id) AS evaluaciones
  FROM cursos c
  JOIN school_years sy ON sy.id = c.school_year_id
  LEFT JOIN niveles n  ON n.id  = c.id_nivel
  WHERE c.id IN (SELECT id FROM cursos_candidatos)
  ORDER BY c.nombre, sy."year";


  -- ============================================================
  -- PASO 2: BORRADO DENTRO DE UNA TRANSACCIÓN
  --         Solo ejecutá si el PASO 1 te mostró lo esperado
  -- ============================================================
  BEGIN;

      -- CTE reutilizable con los IDs a eliminar
      WITH cursos_candidatos AS (
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
      ),

      -- Fechas de asistencia asociadas a esos cursos
      fechas_a_borrar AS (
          SELECT id FROM fechas_curso
          WHERE id_curso IN (SELECT id FROM cursos_candidatos)
      )

      -- 1) Registros de asistencia de esas fechas
      DELETE FROM asistencias
      WHERE id_fechas_curso IN (SELECT id FROM fechas_a_borrar);

      -- 2) Fechas de asistencia
      DELETE FROM fechas_curso
      WHERE id_curso IN (
          SELECT c.id FROM cursos c
          JOIN (
              SELECT nombre FROM cursos WHERE deleted_at IS NULL
              GROUP BY nombre HAVING COUNT(DISTINCT school_year_id) > 1
          ) rep ON rep.nombre = c.nombre
          WHERE c.deleted_at IS NULL
            AND NOT EXISTS (SELECT 1 FROM alumnos_cursos ac WHERE ac.id_curso = c.id)
      );

      -- 3) Notas de evaluaciones
      DELETE FROM evaluaciones_alumnos
      WHERE id_evaluacion IN (
          SELECT ev.id FROM evaluaciones ev
          WHERE ev.id_curso IN (
              SELECT c.id FROM cursos c
              JOIN (
                  SELECT nombre FROM cursos WHERE deleted_at IS NULL
                  GROUP BY nombre HAVING COUNT(DISTINCT school_year_id) > 1
              ) rep ON rep.nombre = c.nombre
              WHERE c.deleted_at IS NULL
                AND NOT EXISTS (SELECT 1 FROM alumnos_cursos ac WHERE ac.id_curso = c.id)
          )
      );

      -- 4) Evaluaciones
      DELETE FROM evaluaciones
      WHERE id_curso IN (
          SELECT c.id FROM cursos c
          JOIN (
              SELECT nombre FROM cursos WHERE deleted_at IS NULL
              GROUP BY nombre HAVING COUNT(DISTINCT school_year_id) > 1
          ) rep ON rep.nombre = c.nombre
          WHERE c.deleted_at IS NULL
            AND NOT EXISTS (SELECT 1 FROM alumnos_cursos ac WHERE ac.id_curso = c.id)
      );

      -- 5) Finalmente los cursos
      DELETE FROM cursos
      WHERE id IN (
          SELECT c.id FROM cursos c
          JOIN (
              SELECT nombre FROM cursos WHERE deleted_at IS NULL
              GROUP BY nombre HAVING COUNT(DISTINCT school_year_id) > 1
          ) rep ON rep.nombre = c.nombre
          WHERE c.deleted_at IS NULL
            AND NOT EXISTS (SELECT 1 FROM alumnos_cursos ac WHERE ac.id_curso = c.id)
      );

  COMMIT;
  -- Si algo no está bien antes del COMMIT, ejecutá ROLLBACK; en su lugar

  Flujo de ejecución:

  PASO 1 → revisás el resultado visual
             ↓ todo ok?
           PASO 2 con BEGIN → revisás los DELETE afectados
             ↓ todo ok?
           COMMIT  (o ROLLBACK si algo no cuadra)

  Orden de borrado (crítico para no romper FK):

  asistencias → fechas_curso → evaluaciones_alumnos → evaluaciones → cursos