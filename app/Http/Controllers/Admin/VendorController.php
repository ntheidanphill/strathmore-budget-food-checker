<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Vendor;
use Illuminate\Http\Request;

/**
 * Admin: add, edit and DEACTIVATE vendors (R4).
 * We deactivate instead of deleting so the vendor's history is never lost.
 */
class VendorController extends Controller
{
    /** READ: list all vendors with their location. */
    public function index()
    {
        $vendors = Vendor::with('location')->withCount('menuItems')->orderBy('name')->get();

        return view('admin.vendors.index', compact('vendors'));
    }

    /** Show the empty "add vendor" form. */
    public function create()
    {
        return view('admin.vendors.form', [
            'vendor'    => new Vendor(),   // empty model so the form can be shared with edit
            'locations' => Location::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /** CREATE: validate then save. */
    public function store(Request $request)
    {
        Vendor::create($this->validated($request));

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor added.');
    }

    /** Show the edit form. */
    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.form', [
            'vendor'    => $vendor,
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    /** UPDATE */
    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($this->validated($request, $vendor));

        return redirect()->route('admin.vendors.index')->with('success', 'Vendor updated.');
    }

    /** DEACTIVATE / re-activate with one click. */
    public function toggle(Vendor $vendor)
    {
        $vendor->update(['is_active' => ! $vendor->is_active]);

        return back()->with('success', $vendor->name . ($vendor->is_active ? ' activated.' : ' deactivated.'));
    }

    /** Shared validation rules for store + update (server-side validation, brief 5.4). */
    private function validated(Request $request, ?Vendor $vendor = null): array
    {
        $data = $request->validate([
            // unique:table,column,ignoreId -> name must be unique except for this same vendor
            'name'        => ['required', 'string', 'max:100', 'unique:vendors,name,' . ($vendor?->id ?? 'NULL')],
            'location_id' => ['required', 'exists:locations,id'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
