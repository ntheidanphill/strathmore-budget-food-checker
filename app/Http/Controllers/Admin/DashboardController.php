<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\Vendor;

/** Admin home page: quick counts + shortcut tiles to every admin function (R4). */
class DashboardController extends Controller
{
    public function index()
    {
        $counts = [
            'vendors'    => Vendor::count(),
            'managers'   => User::where('role', User::ROLE_VENDOR)->count(),
            'locations'  => Location::count(),
            'categories' => Category::count(),
            'items'      => MenuItem::count(),
        ];

        return view('admin.dashboard', compact('counts'));
    }
}
