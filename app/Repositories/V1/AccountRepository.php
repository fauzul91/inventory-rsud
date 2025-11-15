<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\AccountRepositoryInterface;
use App\Models\Category;
use App\Models\User;

class AccountRepository implements AccountRepositoryInterface
{
    public function getAllAccount(array $filters)
    {
        $query = User::query();

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
        return $query->paginate($perPage);
    }

    public function edit($id)
    {
        return User::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $account = User::findOrFail($id);
        $account->update($data);

        return $account;
    }
}