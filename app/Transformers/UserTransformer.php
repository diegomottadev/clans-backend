<?php

namespace App\Transformers;

use App\Models\Teacher;
use App\Models\TypeEvaluation;
use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public function transform(User $user)
    {
        return [
            //
            'id' => (int)$user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'role' => (string) $user->role,
            'teacher' => (object) $user->teacher,
        ];
    }

    public static function originalAttributes($index){
        $attributes = [
            //
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'role' => 'role',
            'teacher' => 'teacher',
        ];

        return isset($attributes[$index]) ? $attributes[$index]:null;
    }
}
