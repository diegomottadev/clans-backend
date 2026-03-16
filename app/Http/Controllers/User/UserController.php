<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    //
         /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(
            'auth:api'
        );
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $users = User::query()->with(['teacher'])->orderBy('id','DESC');

        $collections = $request->input('all') == 1
        ? $this->showList($users->get()):
         $this->showAll($users);
         return $collections;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $rules = [
            'name' => 'required|max:60',
            'email'=> 'required|max:60|unique:users,email',
            'password'=> 'required',
            'teacher'=> 'required',
            'role' => 'required'
        ];

        $this->validate($request,$rules);

        $user = User::create([
            "name" => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'id_profesor' => $request->teacher,
            'role' => $request->role
        ]);
        
        // Limpiar caché de usuarios para actualizar la lista
        cache()->forget('users_with_profesor_id');

        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
        $rules = [
            'email' => 'required|max:60|unique:users,email,'. $user->id,
            'name'=> 'required|max:60',
            'teacher'=> 'required',
            'role' => 'required'
        ];

        $this->validate($request,$rules);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->id_profesor = $request->teacher;
        $user->role = $request->role;

        $user->save();
        
        // Limpiar caché de usuarios para actualizar la lista
        cache()->forget('users_with_profesor_id');

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
        $user->id_profesor = null;
        $user->save();
        $user->delete();
        
        // Limpiar caché de usuarios para actualizar la lista
        cache()->forget('users_with_profesor_id');
        
        return $this->showOne($user);
    }
}
