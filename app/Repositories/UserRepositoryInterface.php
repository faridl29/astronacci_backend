<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

interface UserRepositoryInterface
{
    public function getAllUsers(int $perPage = 10, ?string $search = null): LengthAwarePaginator;
    public function getUserById(int $id): ?User;
    public function createUser(array $data): User;
    public function updateUser(int $id, array $data): ?User;
    public function deleteUser(int $id): bool;
    public function getUserByEmail(string $email): ?User;
    public function searchUsers(string $search, int $perPage = 10): LengthAwarePaginator;
}