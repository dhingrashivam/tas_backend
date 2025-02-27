<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Helpers\ApiResponse;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API Documentation",
 *      description="Swagger API documentation for Laravel 11 + JWT",
 *      @OA\Contact(email="your-email@example.com"),
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for Authentication"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/login",
     *      operationId="loginUser",
     *      tags={"Authentication"},
     *      summary="Login a user",
     *      description="Authenticates a user and returns a JWT token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", example="user@example.com"),
     *              @OA\Property(property="password", type="string", example="password123")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Login successful",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Login successful"),
     *              @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1..."),
     *              @OA\Property(property="user", type="object")
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=422, description="Validation error"),
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return ApiResponse::error('Unauthorized', [], 401);
        }

        $user = auth()->user()->load('roles');

        return ApiResponse::success('Login successful', [
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/logout",
     *      operationId="logoutUser",
     *      tags={"Authentication"},
     *      summary="Logout a user",
     *      description="Logs out a user by invalidating the JWT token",
     *      security={{ "bearerAuth": {} }},
     *      @OA\Response(response=200, description="Logout successful",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logout successful")
     *          )
     *      ),
     *      @OA\Response(response=500, description="Logout failed"),
     * )
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return ApiResponse::success('Logout successful');
        } catch (\Exception $e) {
            return ApiResponse::error('Logout failed', ['error' => $e->getMessage()], 500);
        }
    }
}
