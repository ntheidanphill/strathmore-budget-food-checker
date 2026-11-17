<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

/** Admin: add, rename and deactivate food categories (R4). Uses the shared 'lookups' view. */
class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.lookups.index', [
            'title'       => 'Food Categories',
            'icon'        => '🏷️',
            'routePrefix' => 'admin.categories',
            'items'       => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:categories,name']]);
        Category::create($data);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:categories,name,' . $category->id]]);
        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function toggle(Category $category)
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', $category->name . ($category->is_active ? ' activated.' : ' deactivated.'));
    }
}
