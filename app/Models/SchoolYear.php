<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Transformers\SchoolYearTransform;

class SchoolYear extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $transformer = SchoolYearTransform::class;

    protected $table = 'school_years';
	protected $dates = ['created_at','update_at','deleted_at'];

    protected $fillable = [
        'id',
        'year',
        'active'
    ];
}
