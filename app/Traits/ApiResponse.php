<?php

namespace App\Traits;

use App\Models\SchoolYear;
use App\Transformers\AssistanceTransformer;
use App\Transformers\InvoiceTransform;
use App\Transformers\FixedCostTransform;
use App\Transformers\PaymentTeacherTransform;
use App\Transformers\CourseTransform;
use App\Transformers\StudentTransform;

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
                   $collection = $collection->whereHas('student', function($q) use($value){
                                            $q->where('nombreCompleto', 'ilike','%'.$value.'%');
                                });
                }
            }
            else if($transformer == FixedCostTransform::class){
                $attribute = $transformer::originalAttributes($query);

                if(isset($attribute,$value) && $attribute == 'typeExpense'){
                    $collection = $collection->whereHas('typeExpense', function($q) use($value){
                        $q->where('nombre', 'ilike','%'.$value.'%');
                    });
                }
            }
            else if($transformer == PaymentTeacherTransform::class){
                $attribute = $transformer::originalAttributes($query);

                if(isset($attribute,$value) && $attribute == 'teacher'){
                    $collection = $collection->whereHas('teacher', function($q) use($value){
                        $q->where('nombre', 'ilike','%'.$value.'%');
                    });
                }
            }

            else if($transformer == AssistanceTransformer::class){
                $attribute = $transformer::originalAttributes($query);

                if(isset($attribute,$value) && $attribute == 'courseAssistence'){
                    $collection = $collection->whereHas('course', function($q) use($value){
                        $q->where('nombre', 'ilike','%'.$value.'%');
                    });
                }else if(isset($attribute,$value) && $attribute == 'dateAssistence'){
                    $collection = $collection->where('fecha', $value);
                }
            }

            else if($transformer == CourseTransform::class){
                $attribute = $transformer::originalAttributes($query);

                if(isset($attribute,$value) && $attribute == 'name'){
                    $collection = $collection->where('nombre', 'ilike','%'.$value.'%');
                }
            }

            else if($transformer == StudentTransform::class){
                $attribute = $transformer::originalAttributes($query);
                if(isset($attribute,$value)){
                    // activo es boolean: comparación exacta, no ilike
                    if($attribute === 'activo'){
                        $collection = $collection->where('activo', (int) $value);
                    } else {
                        $collection = $collection->where($attribute, 'ilike', '%'.$value.'%');
                    }
                }
            }

            else{
                $attribute = $transformer::originalAttributes($query);
                if(isset($attribute,$value)){
                   $collection = $collection->where($attribute, 'ilike', '%'.$value.'%');;
                }
            }

        }

        return $collection;
    }
}
