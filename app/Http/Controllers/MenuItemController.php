<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * MenuItemController - full CRUD for menu items (R3 + R4).
 *
 *   Vendor Manager -> sees and changes ONLY their own vendor's items
 *   Admin          -> sees and can correct ANY item
 *
 * BUSINESS RULES ENFORCED HERE (on the server, API brief 5.7):
 *   BR1  A Vendor Manager can never view or change another vendor's items.
 *   BR2  A vendor cannot list two items with the same name.
 *   BR3  Prices must be between KES 1 and KES 5,000.
 */
class MenuItemController extends Controller
{
    /** READ: list items with search (q) and status filter (API brief 5.8). */
    public function index(Request $request)
    {
        $items = $this->scopedQuery($request->user())
            ->with(['vendor.location', 'category'])
            ->when($request->filled('q'), fn (Builder $q) => $q->where('name', 'like', '%' . $request->input('q') . '%'))
            ->when($request->input('status') === 'active', fn (Builder $q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn (Builder $q) => $q->where('is_active', false))
            ->orderBy('name')
            ->get();

        return view('items.index', compact('items'));
    }

    /** Show the empty "add item" form. */
    public function create(Request $request)
    {
        return view('items.form', $this->formData($request, new MenuItem()));
    }

    /** CREATE */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        MenuItem::create($data);

        return redirect()->route('items.index')->with('success', 'Menu item added.');
    }

    /** Show the edit form. */
    public function edit(Request $request, MenuItem $item)
    {
        $this->authorizeItem($request, $item);   // BR1

        return view('items.form', $this->formData($request, $item));
    }

    /** UPDATE (e.g. change the price) */
    public function update(Request $request, MenuItem $item)
    {
        $this->authorizeItem($request, $item);   // BR1: checked BEFORE anything is saved
        $item->update($this->validated($request, $item));

        return redirect()->route('items.index')->with('success', 'Menu item updated.');
    }

    /** DELETE */
    public function destroy(Request $request, MenuItem $item)
    {
        $this->authorizeItem($request, $item);   // BR1
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Menu item deleted.');
    }

    /** One-click IsActive stock toggle (R3). */
    public function toggle(Request $request, MenuItem $item)
    {
        $this->authorizeItem($request, $item);   // BR1
        $item->update(['is_active' => ! $item->is_active]);

        return back()->with('success', $item->name . ($item->is_active ? ' is now IN STOCK.' : ' is now OUT OF STOCK.'));
    }

    // ----------------------------------------------------------------------
    //  PRIVATE HELPERS
    // ----------------------------------------------------------------------

    /** Base query: admins get every item, vendor managers get only their own vendor's. */
    private function scopedQuery(User $user): Builder
    {
        $query = MenuItem::query();

        return $user->isAdmin() ? $query : $query->where('vendor_id', $user->vendor_id);
    }

    /** BR1 - the key business rule: block a vendor manager from another vendor's item. */
    private function authorizeItem(Request $request, MenuItem $item): void
    {
        $user = $request->user();

        if ($user->isVendorManager() && $item->vendor_id !== $user->vendor_id) {
            abort(403, 'You can only manage items that belong to your own vendor.');
        }
    }

    /** Data the add/edit form needs. */
    private function formData(Request $request, MenuItem $item): array
    {
        return [
            'item'       => $item,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            // Only admins choose a vendor; managers are locked to theirs
            'vendors'    => $request->user()->isAdmin() ? Vendor::orderBy('name')->get() : collect(),
        ];
    }

    /** Server-side validation shared by store + update. */
    private function validated(Request $request, ?MenuItem $item = null): array
    {
        $user = $request->user();

        abort_if(! $user->isAdmin() && ! $user->vendor_id, 403, 'Your account is not linked to a vendor.');

        // Managers: vendor comes from their ACCOUNT (never from the form, so it cannot be faked).
        // Admins: they pick the vendor in the form.
        $vendorId = $user->isAdmin() ? $request->input('vendor_id') : $user->vendor_id;

        $rules = [
            // BR2: name must be unique within the SAME vendor (ignore this item when editing)
            'name'        => ['required', 'string', 'max:100',
                Rule::unique('menu_items', 'name')->where('vendor_id', $vendorId)->ignore($item?->id)],
            // BR3: sensible price range in KES
            'price'       => ['required', 'numeric', 'min:1', 'max:5000'],
            'category_id' => ['required', 'exists:categories,id'],
        ];

        if ($user->isAdmin()) {
            $rules['vendor_id'] = ['required', 'exists:vendors,id'];
        }

        $data = $request->validate($rules);
        $data['vendor_id'] = $vendorId;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
