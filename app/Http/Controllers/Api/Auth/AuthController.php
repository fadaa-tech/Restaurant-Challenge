<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Services\Auth\LoginService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\UserCreationService;
use App\Http\Requests\Auth\UserCreateRequest;

class AuthController extends Controller
{
    protected $loginUser;
    protected $userCreationService;

    public function __construct(LoginService $loginUser, UserCreationService $userCreationService)
    {
        $this->loginUser = $loginUser;
        $this->userCreationService = $userCreationService;
    }

    public function createUser(UserCreateRequest $request)
    {
        $data = $request->validated();
        $user = $this->userCreationService->createUser($data);
        return response()->json(['user' => $user], 201);
    }

    public function loginUser(LoginRequest $request)
    {
        $credentials = $request->validated();
        $user = $this->loginUser->loginUser($credentials);

        if ($user) {
            return response()->json(['user' => $user], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
