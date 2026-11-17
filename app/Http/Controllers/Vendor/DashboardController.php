<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Vendor Manager home page: a quick summary of THEIR menu (R3).
 * Only the logged-in manager's own vendor is ever queried.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // A manager with no vendor linked cannot use the dashboard (Admin must fix the account)
        abort_if(! $user->vendor_id, 403, 'Your account is not linked to a vendor. Contact the administrator.');

        $vendor = $user->vendor()->with('location')->first();

        // Quick metrics shown on the dashboard cards
        $stats = [
            'total'    => $vendor->menuItems()->count(),
            'active'   => $vendor->menuItems()->where('is_active', true)->count(),
            'inactive' => $vendor->menuItems()->where('is_active', false)->count(),
            'average'  => (int) round($vendor->menuItems()->avg('price') ?? 0),
        ];

        // The 5 most recently changed items
        $recent = $vendor->menuItems()->with('category')->latest('updated_at')->take(5)->get();

        return view('vendor.dashboard', compact('vendor', 'stats', 'recent'));
    }
}
