<?php

namespace Volistx\FrameworkKernel\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Volistx\FrameworkKernel\Models\User;

class UserRepository
{
    /**
     * Create a new user.
     *
     * @param array $inputs [user_id]
     */
    public function Create(array $inputs): Model|Builder
    {
        return User::query()->create([
            'id' => $inputs['user_id'] ?? Str::ulid()->toRfc4122(),
            'is_active' => true,
        ]);
    }

    /**
     * Update an existing user.
     *
     * @param array $inputs [is_active]
     */
    public function Update(string $userId, array $inputs): ?object
    {
        $user = $this->Find($userId);

        if (!$user) {
            return null;
        }

        if (array_key_exists('is_active', $inputs)) {
            $user->is_active = $inputs['is_active'];
        }

        $user->save();

        return $user;
    }

    /**
     * Find a user by ID.
     */
    public function Find(string $userId): ?object
    {
        return User::query()->where('id', $userId)->first();
    }

    /**
     * Delete a user by ID.
     */
    public function Delete(string $userId): ?bool
    {
        $toBeDeletedUser = $this->find($userId);

        if (!$toBeDeletedUser) {
            return null;
        }

        try {
            $toBeDeletedUser->delete();

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Find all users with pagination support.
     */
    public function FindAll(string $search, int $page, int $limit): ?LengthAwarePaginator
    {
        // Handle empty search
        if ($search === '') {
            $search = 'id:';
        }

        if (!str_contains($search, ':')) {
            return null;
        }

        $columns = Schema::getColumnListing('users');
        $values = explode(':', $search, 2);
        $columnName = strtolower(trim($values[0]));

        if (!in_array($columnName, $columns)) {
            return null;
        }

        $searchValue = strtolower(trim($values[1]));

        return User::query()
            ->where($values[0], 'LIKE', "%$searchValue%")
            ->paginate($limit, ['*'], 'page', $page);
    }
}
