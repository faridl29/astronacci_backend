<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getAllUsers(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = $this->model->query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getUserById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->model->create($data);
    }

    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->getUserById($id);
        
        if (!$user) {
            return null;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);
        return $user ? $user->delete() : false;
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function searchUsers(string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->getAllUsers($perPage, $search);
    }
}