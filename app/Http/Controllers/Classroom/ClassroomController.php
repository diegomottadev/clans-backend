<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\ApiController;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $classrooms = Classroom::query()->orderBy('nombre', 'ASC');
        $collections = $request->input('all', '') == 1
            ? $this->showList($classrooms->get())
            : $this->showAll($classrooms);
        return $collections;
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:100',
            'capacity' => 'nullable|integer|min:1',
        ];

        $this->validate($request, $rules);

        $classroom = Classroom::create([
            'nombre' => $request->name,
            'capacidad' => $request->capacity,
        ]);

        return $this->showOne($classroom);
    }

    public function update(Request $request, Classroom $classroom)
    {
        $rules = [
            'name' => 'required|max:100',
            'capacity' => 'nullable|integer|min:1',
        ];

        $this->validate($request, $rules);

        $classroom->nombre = $request->name;
        $classroom->capacidad = $request->capacity;
        $classroom->save();

        return $this->showOne($classroom);
    }

    public function destroy(Classroom $classroom)
    {
        $classroom->delete();
        return $this->showOne($classroom);
    }
}
