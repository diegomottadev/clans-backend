---
name: laravel-clans
description: Genera código para el proyecto Clans siguiendo sus convenciones. Úsalo cuando necesites crear controllers, models, transformers, migrations o cualquier pieza de código Laravel para este proyecto.
argument-hint: [qué generar, ej: "controller para TypeCourse" o "model para Payment"]
---

Sos un experto en este proyecto Laravel 8. Generá código siguiendo estrictamente las convenciones del proyecto.

## Stack
- Laravel 8, PHP 7.3/8.0
- PostgreSQL (tablas en español, ej: `alumnos`, `cursos`, `profesores`)
- JWT Auth (`tymon/jwt-auth`) con middleware `isAdmin` e `isUser`
- Fractal (`spatie/laravel-fractal`) para transformar respuestas
- SoftDeletes en la mayoría de modelos

## Convenciones

### Controllers
- Siempre extender `ApiController` (no `Controller`)
- Usar `$this->showOne()`, `$this->showAll()`, `$this->showList()` del trait `ApiResponse`
- Carpeta por dominio: `app/Http/Controllers/<Dominio>/`
- Validar con `$this->validate($request, $rules)`

```php
namespace App\Http\Controllers\Dominio;

use App\Http\Controllers\ApiController;
use App\Models\MyModel;
use Illuminate\Http\Request;

class MyController extends ApiController
{
    public function index(Request $request)
    {
        $items = MyModel::query();
        $collections = $request->input('all','') == 1
            ? $this->showList($items->get())
            : $this->showAll($items);
        return $collections;
    }

    public function show(MyModel $model)
    {
        return $this->showOne($model);
    }

    public function store(Request $request)
    {
        $rules = ['campo' => 'required'];
        $this->validate($request, $rules);
        $item = MyModel::create([...]);
        return $this->showOne($item);
    }

    public function update(Request $request, MyModel $model)
    {
        $model->campo = $request->campo;
        $model->save();
        return $this->showOne($model);
    }

    public function destroy(MyModel $model)
    {
        $model->delete();
        return $this->showOne($model);
    }
}
```

### Models
- Siempre declarar `public $transformer = MiTransformer::class`
- Tabla en español con `protected $table = 'nombre_tabla'`
- Usar `SoftDeletes` salvo que sea una tabla pivot
- `$dates = ['created_at', 'updated_at', 'deleted_at']`

```php
namespace App\Models;

use App\Transformers\MyTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MyModel extends Model
{
    use HasFactory, SoftDeletes;

    public $transformer = MyTransformer::class;
    protected $table = 'nombre_tabla_en_español';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['campo1', 'campo2'];
}
```

### Transformers
- Extender `TransformerAbstract` de Fractal
- Siempre implementar `originalAttributes()` para el filtrado
- Mapear nombres de DB (español) a nombres de API (inglés)

```php
namespace App\Transformers;

use App\Models\MyModel;
use League\Fractal\TransformerAbstract;

class MyTransformer extends TransformerAbstract
{
    public function transform(MyModel $model)
    {
        return [
            'id'         => (int) $model->id,
            'name'       => (string) $model->nombre,
            'created_at' => (string) $model->created_at,
            'updated_at' => (string) $model->updated_at,
            'deleted_at' => isset($model->deleted_at) ? (string) $model->deleted_at : null,
        ];
    }

    public static function originalAttributes($index)
    {
        $attributes = [
            'id'   => 'id',
            'name' => 'nombre',
        ];
        return $attributes[$index] ?? null;
    }
}
```

### Migrations
- Nombres de tabla en español
- Siempre incluir `$table->softDeletes()` si el model lo usa
- Puerto PostgreSQL, usar tipos compatibles

```php
Schema::create('nombre_tabla', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->softDeletes();
    $table->timestamps();
});
```

### Rutas (routes/api.php)
- Agrupar con `Route::resource()` especificando solo los métodos necesarios
- Proteger con middleware `isAdmin` o `isUser`

```php
Route::resource('myResources', MyController::class, ['only' => ['index','store','update','destroy']])
    ->middleware(['isUser']);
```

## Tarea
$ARGUMENTS
