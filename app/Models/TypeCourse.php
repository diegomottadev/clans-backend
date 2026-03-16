<?php

namespace App\Models;

use App\Transformers\TypeCourseTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Level;

class TypeCourse extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = TypeCourseTransform::class;

    protected $table = 'tipos_cursado';

	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
    	'id',
    	'nombre',
    ];

    public function level(){
        return $this->hasOne(Level::class);
    }

}
