<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

/** Admin: add, rename and deactivate campus locations (R4). Uses the shared 'lookups' view. */
class LocationController extends Controller
{
    public function index()
    {
        return view('admin.lookups.index', [
            'title'       => 'Locations',
            'icon'        => '📍',
            'routePrefix' => 'admin.locations',
            'items'       => Location::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:locations,name']]);
        Location::create($data);

        return back()->with('success', 'Location added.');
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:locations,name,' . $location->id]]);
        $location->update($data);

        return back()->with('success', 'Location updated.');
    }

    public function toggle(Location $location)
    {
        $location->update(['is_active' => ! $location->is_active]);

        return back()->with('success', $location->name . ($location->is_active ? ' activated.' : ' deactivated.'));
    }
}
