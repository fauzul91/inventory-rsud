<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAllCategoriesForSelect()
    {
        return Category::select('id', 'name')->orderBy('name', 'asc')->get();
    }
    public function getAllCategories(array $filters)
    {
        $query = Category::query();

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
    public function create(array $data)
    {
        return Category::create($data);
    }

    public function edit($id)
    {
        return Category::findOrFail($id);
    }

    public function update(array $data, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category;
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        return $category->delete();
    }
}