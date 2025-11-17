<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\AccountRepositoryInterface;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

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
            return [
                'id' => $user->id,
                'name' => $user->name,
                'sso_id' => $user->sso_id,
                'email' => $user->email,
                'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                'roles' => $user->roles->pluck('name'),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return $paginated;
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'sso_id' => $user->sso_id,
            'email' => $user->email,
            'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
            'roles' => $user->roles->pluck('name'), // langsung array nama role
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    public function update(array $data, $id)
    {
        $account = User::findOrFail($id);

        // Prepare update data
        $updateData = [];

        // Handle name update
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        // Handle photo upload
        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old photo if exists
            if ($account->photo && Storage::disk('public')->exists($account->photo)) {
                Storage::disk('public')->delete($account->photo);
            }

            $updateData['photo'] = $this->handlePhotoUpload($data['photo']);
        }

        // Update account data only if there's something to update
        if (!empty($updateData)) {
            $account->update($updateData);
        }

        // Handle role update
        if (isset($data['role'])) {
            $roles = is_array($data['role']) ? $data['role'] : [$data['role']];
            $account->syncRoles($roles);
        }

        // Refresh and load relationships
        $account->refresh()->load('roles');
        $account->roles = $account->roles->pluck('name');

        return $account;
    }

    /**
     * Handle upload photo
     */
    private function handlePhotoUpload($file)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('photos', $filename, 'public');
    }
}