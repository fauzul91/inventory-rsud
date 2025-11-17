<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\AccountRepositoryInterface;
use App\Models\Category;
use App\Models\User;

class AccountRepository implements AccountRepositoryInterface
{
    public function getAllAccount(array $filters)
    {
        $query = User::with('roles');

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'latest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($filters['sort_by'] === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $perPage = $filters['per_page'] ?? 10;
        $paginated = $query->paginate($perPage);

        $paginated->getCollection()->transform(function ($user) {
            $user->roles = $user->roles->pluck('name');
            return $user;
        });

        return $paginated;
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $user->roles = $user->roles->pluck('name'); // ambil nama role saja
        return $user;
    }

    public function update(array $data, $id)
    {
        $account = User::findOrFail($id);

        if (!empty($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['photo'] = $this->handlePhotoUpload($data['photo']);
        }

        $account->update(array_filter([
            'name' => $data['name'] ?? null,
            'photo' => $data['photo'] ?? null,
        ]));

        if (!empty($data['role'])) {
            $roles = is_array($data['role']) ? $data['role'] : [$data['role']];
            $account->syncRoles($roles);
        }

        $account->load('roles');
        $account->roles = $account->roles->pluck('name');

        return $account;
    }

    /**
     * Handle upload photo
     */
    private function handlePhotoUpload($file)
    {
        return $file->store('photos', 'public');
    }
}