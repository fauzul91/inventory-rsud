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
}