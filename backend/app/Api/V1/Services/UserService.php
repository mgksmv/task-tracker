<?php

namespace App\Api\V1\Services;

use App\DTO\UserFilters;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserService
{
    public function getUsers(UserFilters $filters, ?int $perPage = null): LengthAwarePaginator|Collection
    {
        $query = User::query()
            ->when(
                $filters->sort_field,
                function ($query) use ($filters) {
                    $query->orderBy($filters->sort_field, $filters->sort_order);
                },
                function ($query) {
                    $query->latest();
                },
            )
            ->when($filters->name, function ($query) use ($filters) {
                $query->whereLike('name', "%$filters->name%");
            })
            ->when($filters->email, function ($query) use ($filters) {
                $query->whereLike('email', "%$filters->email%");
            });

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function saveUser(array $data, ?User $user = null): User
    {
        if ($user && empty($data['password'])) {
            unset($data['password']);
        }

        if ($user) {
            $user->update($data);
        } else {
            $user = User::query()->create($data);
        }

        return $user;
    }
}
