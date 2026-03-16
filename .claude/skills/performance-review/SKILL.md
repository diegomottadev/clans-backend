---
name: performance-review
description: Analiza y corrige problemas de performance en el código Laravel del proyecto Clans. Detecta N+1, consultas ineficientes, eager loading faltante, uso excesivo de memoria y cuellos de botella en Eloquent y PostgreSQL. Úsalo cuando quieras revisar o mejorar el rendimiento de un controller, model, o query.
argument-hint: [archivo o funcionalidad a revisar, ej: "ReportController" o "listado de estudiantes"]
---

Sos un experto en performance de Laravel 8 con PostgreSQL. Analizá el código indicado y detectá todos los problemas de rendimiento, luego proponé y aplicá las correcciones.

## Contexto del proyecto
- Laravel 8, PostgreSQL
- Fractal transformers en todos los modelos (`$model->transformer`)
- Paginación de 10 registros por defecto en `ApiResponse::showAll()`
- Tablas principales: `alumnos`, `cursos`, `asistencias`, `facturas`, `evaluaciones`, `profesores`
- `Course` model tiene `protected $with = ['level', 'schoolYear']` (eager loading global — puede ser problema)

## Qué revisar

### 1. Problema N+1
Buscá loops que hagan queries dentro:
```php
// MAL - N+1
foreach ($students as $student) {
    $student->courses; // query por cada alumno
}

// BIEN
$students = Student::with('courses')->get();
```

Señales de N+1:
- `->get()` sin `->with()` cuando el transformer accede a relaciones
- Relaciones accedidas dentro de `transform()` sin haberlas cargado
- `$model->relation` dentro de loops o transformers sin eager load

### 2. Eager loading innecesario o excesivo
```php
// MAL - carga relaciones que no se usan
->with(['student', 'student.courses', 'studentCourse', 'studentCourse.student.courses'])

// BIEN - solo lo necesario
->with(['student', 'studentCourse.course'])
```

También revisá `protected $with` en models — carga siempre aunque no se necesite.

### 3. Select de columnas innecesarias
```php
// MAL - trae todas las columnas
Student::with('courses')->get();

// BIEN
Student::select('id','nombre','apellido','dni')->with('courses:id,nombre')->get();
```

### 4. Consultas en transformers
Los transformers de este proyecto acceden a relaciones. Verificar que estén pre-cargadas:
```php
// En StudentTransform::transform() se accede a $student->courses
// El controller DEBE hacer ->with('courses') antes
```

### 5. Queries sin índices (PostgreSQL)
Revisá filtros frecuentes que no tengan índice:
```php
// Filtros comunes en este proyecto que necesitan índice:
// alumnos.nombreCompleto (usado en búsquedas ilike)
// facturas.fecha_emision (usado en whereYear, orderBy)
// asistencias.fecha
// cursos.id_profesor, cursos.id_nivel
```

### 6. `whereYear()` ineficiente en PostgreSQL
```php
// MAL - no usa índice, convierte cada fila
->whereYear('fecha_emision', $year)

// BIEN - rango de fechas, usa índice
->whereBetween('fecha_emision', ["{$year}-01-01", "{$year}-12-31"])
```

### 7. Paginación vs `->get()` en colecciones grandes
```php
// MAL para colecciones grandes
$items = Model::with('rel')->get(); // trae todo a memoria

// BIEN
$items = Model::with('rel')->paginate(10);
// o con query builder cuando se puede
$items = Model::with('rel'); // pasar el query builder a showAll()
```

### 8. `count()` vs `exists()`
```php
// MAL
if ($model->relation->count() > 0)

// BIEN
if ($model->relation()->exists())
```

### 9. Chunks para operaciones masivas (reportes)
```php
// MAL en reportes grandes
$all = Student::with(['assitences','evaluations'])->get();

// BIEN
Student::with(['assitences','evaluations'])->chunk(100, function ($students) use (&$data) {
    foreach ($students as $student) {
        $data[] = [...];
    }
});
```

### 10. Raw SQL con DB::raw — verificar que use índices
```php
// Patrón existente en DebtorController - asegurarse de que date_part tenga soporte de índice
// En PostgreSQL, considerar columnas generadas o índices funcionales para date_part frecuentes
```

## Proceso de análisis

1. **Leé el archivo indicado** completamente
2. **Identificá cada problema** con número de línea y tipo
3. **Mostrá un resumen** de problemas encontrados antes de corregir
4. **Aplicá las correcciones** una por una con explicación
5. **Verificá** que las relaciones corregidas existan en los models

## Formato del reporte

```
## Problemas encontrados en [archivo]

### CRÍTICO - N+1 en línea X
[descripción + código actual + código corregido]

### MODERADO - Select innecesario en línea X
[descripción + código actual + código corregido]

### SUGERENCIA - Índice faltante para campo X
[descripción + migration sugerida]
```

## Tarea
Analizá y corregí los problemas de performance en: $ARGUMENTS
