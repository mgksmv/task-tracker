<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\UserAllRequest;
use App\Api\V1\Requests\UserIndexRequest;
use App\Api\V1\Requests\UserSaveRequest;
use App\Api\V1\Resources\UserResource;
use App\Api\V1\Services\UserService;
use App\DTO\UserFilters;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Group('Пользователи')]
class UserController extends ApiController
{
    public function __construct(
        protected UserService $userService,
    ) {
    }

    /**
     * Получить список пользователей с пагинацией
     */
    public function index(UserIndexRequest $request): JsonResponse
    {
        $users = $this->userService->getUsers(UserFilters::from($request), self::DEFAULT_PER_PAGE);

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => $this->paginationMeta($users),
        ]);
    }

    /**
     * Получить список всех пользователей
     */
    public function getAll(UserAllRequest $request): JsonResponse
    {
        $users = $this->userService->getUsers(UserFilters::from($request));

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * Получить пользователя
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => UserResource::make($user),
        ]);
    }

    /**
     * Создать пользователя
     */
    public function store(UserSaveRequest $request): JsonResponse
    {
        $user = $this->userService->saveUser($request->validated());

        return response()->json([
            'success' => true,
            'data' => UserResource::make($user),
        ], Response::HTTP_CREATED);
    }

    /**
     * Обновить пользователя
     */
    public function update(UserSaveRequest $request, User $user): JsonResponse
    {
        $this->userService->saveUser($request->validated(), $user);

        return response()->json([
            'success' => true,
            'data' => UserResource::make($user),
        ]);
    }

    /**
     * Удалить пользователя
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
