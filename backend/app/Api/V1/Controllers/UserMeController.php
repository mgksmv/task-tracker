<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\UserMeUpdatePasswordRequest;
use App\Api\V1\Requests\UserMeUpdateRequest;
use App\Api\V1\Resources\UserResource;
use App\Api\V1\Services\UserService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

#[Group('Пользователи')]
class UserMeController extends ApiController
{
    public function __construct(
        protected UserService $userService,
    ) {
    }

    /**
     * Получить текущего авторизованного пользователя
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => UserResource::make(auth()->user()),
        ]);
    }

    /**
     * Обновить данные текущего авторизованного пользователя
     */
    public function update(UserMeUpdateRequest $request): JsonResponse
    {
        $user = $this->userService->saveUser($request->validated(), auth()->user());

        return response()->json([
            'success' => true,
            'data' => UserResource::make($user),
        ]);
    }

    /**
     * Обновить пароль текущего авторизованного пользователя
     */
    public function updatePassword(UserMeUpdatePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return response()->json([
            'success' => true,
            'data' => UserResource::make($user),
        ]);
    }
}
