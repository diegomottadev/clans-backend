<?php

namespace App\Models;

use App\Transformers\PaymentTeacherTransform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Teacher;

class PaymentTeacher extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = PaymentTeacherTransform::class;
    protected $table = 'pagos_profesores';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
        'id',
        'fecha',
        'id_profesor',
        'monto',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_profesor');
    }

}
