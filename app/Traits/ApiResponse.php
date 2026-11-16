<?php

namespace App\Traits;

use App\Models\SchoolYear;
use App\Transformers\AssistanceTransformer;
use App\Transformers\InvoiceTransform;
use App\Transformers\FixedCostTransform;
use App\Transformers\PaymentTeacherTransform;
use App\Transformers\CourseTransform;
use App\Transformers\StudentTransform;
use App\Transformers\TeacherTransform;

use Exception;
use Illuminate\Support\Collection;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
trait ApiResponse{

    protected function lastYear(){
        $SchoolYear = SchoolYear::orderBy('id','DESC')->first();
        return $SchoolYear;
    }

    protected function successResponse($data,$code){
        return response()->json($data,$code);
    }

    protected function showList( $collection,$code= 200){
        if(!$collection->first()){
            return  $this->successResponse(['data'=>$collection],$code);
        }
        $transformer = $collection->first()->transformer;
        $collection = $this->transformData($collection,$transformer);

        return $this->successResponse(['data'=>$collection],$code);
    }


    protected function showAll( $collection,$code= 200){

        $first = $collection->first();
        $transformer = null;

        if ($first) {
            if (isset($first->transformer) == false) {
                Log::channel('single')->warning('[ApiResponse::showAll] Modelo sin transformer');
                return $this->successResponse(['data' => $collection], $code);
            }
            $transformer = $first->transformer;
        } elseif ($collection instanceof \Illuminate\Database\Eloquent\Builder) {
            // Query Builder sin resultados: usar transformer del modelo para devolver estructura paginada vacía correcta
            $model = $collection->getModel();
            if (isset($model->transformer)) {
                $transformer = $model->transformer;
                Log::channel('single')->info('[ApiResponse::showAll] Sin resultados (query vacía), usando transformer del modelo para respuesta paginada');
            }
        }

        if (!$transformer) {
            Log::channel('single')->info('[ApiResponse::showAll] Sin resultados: collection->first() es null, devolviendo data vacía');
            $empty = ['data' => [], 'meta' => ['pagination' => [
                'total' => 0,
                'count' => 0,
                'per_page' => 10,
                'current_page' => 1,
                'total_pages' => 1,
                'links' => (object) [],
            ]]];
            return $this->successResponse(['data' => $empty], $code);
        }

        $collection = $this->filterData($collection,$transformer);
        if ($collection instanceof \Illuminate\Support\Collection){
            $collection = $this->paginate($collection);
        }else{
            $collection = $this->applySort($collection, $transformer);
            $collection = $collection->paginate(10);
        }
        Log::channel('single')->info('[ApiResponse::showAll] Tras paginar', [
            'total' => $collection->total(),
            'count' => $collection->count(),
            'current_page' => $collection->currentPage(),
        ]);
        $collection = $this->transformData($collection,$transformer);
        return $this->successResponse(['data'=>$collection],$code);
    }

    protected function showRelationshipPaginate($collection,$transformer,$code= 200){

        try {

            $collection = $this->filterData($collection,$transformer);
            //if ($collection instanceof \Illuminate\Pagination\LengthAwarePaginator){}
            $collection = $collection->paginate(10);
            $collection = $this->transformData($collection,$transformer);

            return $this->successResponse(['data'=>$collection],$code);
        }
        catch (Exception $e){
            return $this->successResponse(['data'=>[]],$code);
        }

    }

    protected function showOne(Model $instance = null,$code= 200){
        if ($instance!=null){
            $transformer = $instance->transformer;
            $instance = $this->transformData($instance,$transformer);
            return $this->successResponse($instance,$code);
        }else{
            return $this->successResponse($instance,$code);
        }
    }

    protected function errorResponse($message,$code){
        return response()->json(['message'=> $message,'code'=>$code],$code);
    }

    protected function transformData($data,$tranformer){
        $transformation = fractal($data,new $tranformer);
        return $transformation->toArray();
    }
    //ordena las colecciones por attribute que se le mande
    protected function sortData( $collection,$transformer ){
        if(request()->has('sort_by')){
            $attribute = $transformer::originalAttributes(request()->sort_by);
            $collection = $collection->sort_by->{$attribute};
        }
        return $collection;
    }

    /**
     * Ordenamiento server-side para tablas lazy.
     * Recibe ?sort_by=<campo del frontend>&sort_order=asc|desc.
     * Solo ordena por columnas reales (validadas contra el esquema), nunca SQL arbitrario.
     * Soporta campos propios ("name") y de una relación belongsTo ("course.nombre", "teacher.data.completeName").
     */
    protected function applySort($builder, $transformer)
    {
        $sortBy = request()->query('sort_by');
        if (!is_string($sortBy) || $sortBy === '' || !($builder instanceof \Illuminate\Database\Eloquent\Builder)) {
            return $builder;
        }
        $dir = strtolower((string) request()->query('sort_order')) === 'desc' ? 'desc' : 'asc';
        $model = $builder->getModel();
        $table = $model->getTable();
        $parts = explode('.', str_replace('.data.', '.', $sortBy));

        $expression = (count($parts) === 1 && $transformer && method_exists($transformer, 'sortExpression'))
            ? $transformer::sortExpression($parts[0])
            : null;

        if ($expression) {
            // Expresión fija definida en el transformer (nunca viene del request)
            $builder->reorder()->orderByRaw('(' . $expression . ') ' . $dir . ' NULLS LAST');
        } elseif (count($parts) === 1) {
            $cols = $this->resolveSortColumns($model, $transformer, $parts[0]);
            if (!$cols) {
                return $builder;
            }
            $builder->reorder();
            foreach ($cols as $col) {
                // NULLS LAST: los vacíos siempre al final, en ambos sentidos
                $builder->orderByRaw('"' . $table . '"."' . $col . '" ' . $dir . ' NULLS LAST');
            }
        } elseif (count($parts) === 2 && method_exists($model, $parts[0])) {
            $relation = $model->{$parts[0]}();
            if (!($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo)) {
                return $builder;
            }
            $related = $relation->getRelated();
            $cols = $this->resolveSortColumns($related, $related->transformer ?? null, $parts[1]);
            if (!$cols) {
                return $builder;
            }
            $relTable = $related->getTable();
            $expr = count($cols) > 1
                ? \DB::raw("concat_ws(' ', " . implode(', ', array_map(function ($c) use ($relTable) { return '"' . $relTable . '"."' . $c . '"'; }, $cols)) . ')')
                : $relTable . '.' . $cols[0];
            $sub = $related->newQuery()
                ->select($expr)
                ->whereColumn($relTable . '.' . $relation->getOwnerKeyName(), $table . '.' . $relation->getForeignKeyName())
                ->limit(1);
            $builder->reorder()->orderByRaw('(' . $sub->toSql() . ') ' . $dir . ' NULLS LAST', $sub->getBindings());
        } else {
            return $builder;
        }

        // Desempate estable para que la paginación no repita/salte filas
        return $builder->orderBy($table . '.' . $model->getKeyName(), 'desc');
    }

    /** Traduce un campo del frontend a columna(s) reales de la tabla del modelo. */
    protected function resolveSortColumns($model, $transformer, $field)
    {
        $table = $model->getTable();
        $has = function ($col) use ($table) {
            return is_string($col) && \Illuminate\Support\Facades\Schema::hasColumn($table, $col);
        };
        if ($field === 'apellido_nombre_completo' && $has('apellido') && $has('nombre')) {
            return ['apellido', 'nombre'];
        }
        $candidates = [];
        if ($transformer && method_exists($transformer, 'originalAttributes')) {
            $candidates[] = $transformer::originalAttributes($field);
        }
        $candidates[] = $field;
        foreach ($candidates as $col) {
            if ($has($col)) {
                return [$col];
            }
        }
        if ($field === 'completeName' && $has('apellido') && $has('nombre')) {
            return ['apellido', 'nombre'];
        }
        return null;
    }

    protected function paginate(Collection $collection){
        //permitiendo el paginado personalizado
        $rules = [
            'per_page' => 'integer|min:2|max:50'
        ];
        Validator::validate(request()->all(),$rules);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        if(request()->has('per_page')){
            $perPage = (int)request()->per_page;
        }
        $result = $collection->slice(($page -1) * $perPage,$perPage)->values();
        $paginate  = new LengthAwarePaginator($result,$collection->count(),$perPage,$page,['path'=>LengthAwarePaginator::resolveCurrentPath()]);
        $paginate->appends(request()->all());
        return $paginate;
    }

    protected function filterData( $collection,$transformer ){

        foreach(request()->query() as  $query => $value){

            if ($transformer ==  InvoiceTransform::class){
                $attribute = $transformer::originalAttributes($query);

                if(isset($attribute,$value) && $attribute == 'student'){
                    $term = '%' . $value . '%';
                    $collection = $collection->whereHas('student', function($q) use($term){
                        $q->whereRaw("UPPER(unaccent(\"nombreCompleto\")) LIKE UPPER(unaccent(?))", [$term]);
                    });
                }
            }
            else if($transformer == FixedCostTransform::class){
                $attribute = $transformer::originalAttributes($query);

                if(isset($attribute,$value) && $attribute == 'typeExpense'){
                    $term = '%' . $value . '%';
                    $collection = $collection->whereHas('typeExpense', function($q) use($term){
                        $q->whereRaw("UPPER(unaccent(nombre)) LIKE UPPER(unaccent(?))", [$term]);
                    });
                }
            }
            else if($transformer == PaymentTeacherTransform::class){
                $attribute = $transformer::originalAttributes($query);

                if(isset($attribute,$value) && $attribute == 'teacher'){
                    $term = '%' . $value . '%';
                    // Nombre, apellido o combinaciones "nombre apellido" / "apellido nombre"
                    $collection = $collection->whereHas('teacher', function($q) use($term){
                        $q->whereRaw("UPPER(unaccent(nombre || ' ' || apellido)) LIKE UPPER(unaccent(?))", [$term])
                          ->orWhereRaw("UPPER(unaccent(apellido || ' ' || nombre)) LIKE UPPER(unaccent(?))", [$term]);
                    });
                }
            }

            else if($transformer == AssistanceTransformer::class){
                $attribute = $transformer::originalAttributes($query);

                if(isset($attribute,$value) && $attribute == 'courseAssistence'){
                    // Valor numérico = id de curso (desplegable); texto = búsqueda por nombre
                    if (is_numeric($value)) {
                        $collection = $collection->where('id_curso', (int) $value);
                    } else {
                        $term = '%' . $value . '%';
                        $collection = $collection->whereHas('course', function($q) use($term){
                            $q->whereRaw("UPPER(unaccent(nombre)) LIKE UPPER(unaccent(?))", [$term]);
                        });
                    }
                }else if(isset($attribute,$value) && $attribute == 'dateAssistence'){
                    $collection = $collection->where('fecha', $value);
                }
            }

            else if($transformer == CourseTransform::class){
                $attribute = $transformer::originalAttributes($query);

                // originalAttributes('name') devuelve 'nombre'
                if(isset($attribute,$value) && $attribute == 'nombre'){
                    $term = '%' . $value . '%';
                    $collection = $collection->whereRaw("UPPER(unaccent(cursos.nombre)) LIKE UPPER(unaccent(?))", [$term]);
                }
            }

            else if($transformer == TeacherTransform::class){
                $attribute = $transformer::originalAttributes($query);
                if(isset($attribute,$value) && $attribute == 'nombreCompleto'){
                    $term = '%' . $value . '%';
                    $collection = $collection->where(function ($q) use ($term) {
                        $q->whereRaw("UPPER(unaccent(nombre || ' ' || apellido)) LIKE UPPER(unaccent(?))", [$term])
                          ->orWhereRaw("UPPER(unaccent(apellido || ' ' || nombre)) LIKE UPPER(unaccent(?))", [$term]);
                    });
                } elseif(isset($attribute,$value)) {
                    $collection = $collection->whereRaw('UPPER(unaccent(CAST("' . $attribute . '" AS TEXT))) LIKE UPPER(unaccent(?))', ['%'.$value.'%']);
                }
            }

            else if($transformer == StudentTransform::class){
                $attribute = $transformer::originalAttributes($query);
                if(isset($attribute,$value)){
                    if($attribute === 'activo'){
                        $collection = $collection->where('activo', (int) $value);
                    } elseif($attribute === 'nombreCompleto'){
                        // Busca sin acentos ni distinción de mayúsculas, en ambas combinaciones nombre/apellido
                        $term = '%' . $value . '%';
                        $collection = $collection->where(function ($q) use ($term) {
                            $q->whereRaw("UPPER(unaccent(\"nombreCompleto\")) LIKE UPPER(unaccent(?))", [$term])
                              ->orWhereRaw("UPPER(unaccent(apellido || ' ' || nombre)) LIKE UPPER(unaccent(?))", [$term]);
                        });
                    } else {
                        $collection = $collection->where($attribute, 'ilike', '%'.$value.'%');
                    }
                }
            }

            else{
                $attribute = $transformer::originalAttributes($query);
                // Solo columnas reales de la tabla (evita errores con relaciones) y con comillas
                // para respetar mayúsculas; CAST a TEXT para soportar números y fechas.
                if(isset($attribute,$value) && is_string($attribute)
                    && $collection instanceof \Illuminate\Database\Eloquent\Builder
                    && \Illuminate\Support\Facades\Schema::hasColumn($collection->getModel()->getTable(), $attribute)){
                    $col = $collection->getModel()->getTable() . '"."' . $attribute;
                    $collection = $collection->whereRaw('UPPER(unaccent(CAST("' . $col . '" AS TEXT))) LIKE UPPER(unaccent(?))', ['%'.$value.'%']);
                }
            }

        }

        return $collection;
    }
}
