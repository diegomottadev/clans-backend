<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
class AuthController extends Controller
{
    //
    //
        /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(
            'auth:api', ['except' => ['login']]
        );
    }
    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $credentials = $request->only(['email', 'password']);
        //if (!$token = auth()->attempt($credentials)) {

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED );
        }
        return $this->respondWithToken($token);
    }
    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $user = cache()->remember('user_' . auth()->id(), 1800, function () {
            return auth()->user()->load('teacher');
        });

        $data = [
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'role'    => $user->role,
            'teacher' => $user->teacher,
        ];

        $etag = '"' . md5(json_encode($data)) . '"';
        $ifNoneMatch = request()->header('If-None-Match');

        if ($ifNoneMatch === $etag) {
            return response()->json(null, 304);
        }

        return response()->json($data)
            ->header('ETag', $etag)
            ->header('Cache-Control', 'private, must-revalidate');
    }
    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        // Limpiar caché del usuario al hacer logout
        cache()->forget('user_' . auth()->id());
        
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::refresh());
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        $user = Auth::user()->load('teacher');
        
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'teacher' => $user->teacher,
            ]
        ]);
    }
}
