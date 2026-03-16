<?php

namespace App\Models;

use App\Transformers\InvoiceTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Student;
use App\Models\StudentCourse;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = InvoiceTransform::class;
    protected $table = 'facturas';
	protected $dates = ['created_at','update_at','deleted_at'];
   // protected $with =   ['student'];


    protected $fillable = [
        'id',
        'id_alumno',
        'id_alumno_curso',
        'fecha_emision',
        'mes',
        'cuota',
        'fecha_vto',
        'dto_pago_termino',
        'dto_hermano',
        'mora',
        'total',
        'estado'
    ];

    public function student(){
        return $this->belongsTo(Student::class,'id_alumno');
    }

    public function studentCourse(){
        return $this->belongsTo(StudentCourse::class,'id_alumno_curso');
    }
}
