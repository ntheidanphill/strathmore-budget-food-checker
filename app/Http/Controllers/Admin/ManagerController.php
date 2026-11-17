<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;

/**
 * Admin: add, edit and DEACTIVATE Vendor Manager accounts (R4).
 * IMPORTANT: we assign the plain password; the User model's 'hashed' cast
 * hashes it automatically. Do NOT call Hash::make() as well (double hashing).
 */
class ManagerController extends Controller
{
    public function index()
    {
        $managers = User::with('vendor')->where('role', User::ROLE_VENDOR)->orderBy('name')->get();

        return view('admin.managers.index', compact('managers'));
    }

    public function create()
    {
        return view('admin.managers.form', [
            'manager' => new User(),
            'vendors' => Vendor::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['role'] = User::ROLE_VENDOR;   // this screen can ONLY create vendor managers

        User::create($data);

        return redirect()->route('admin.managers.index')->with('success', 'Vendor Manager account created.');
    }

    public function edit(User $manager)
    {
        $this->ensureVendorManager($manager);

        return view('admin.managers.form', [
            'manager' => $manager,
            'vendors' => Vendor::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $manager)
    {
        $this->ensureVendorManager($manager);
        $data = $this->validated($request, $manager);

        // Blank password on edit = keep the old one
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $manager->update($data);

        return redirect()->route('admin.managers.index')->with('success', 'Account updated.');
    }

    public function toggle(User $manager)
    {
        $this->ensureVendorManager($manager);
        $manager->update(['is_active' => ! $manager->is_active]);

        return back()->with('success', $manager->username . ($manager->is_active ? ' activated.' : ' deactivated.'));
    }

    /** Safety: this controller must never touch an Admin account. */
    private function ensureVendorManager(User $user): void
    {
        abort_unless($user->isVendorManager(), 404);
    }

    private function validated(Request $request, ?User $manager = null): array
    {
        $id = $manager?->id ?? 'NULL';

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'username'  => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username,' . $id],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $id],
            // Password is required when creating, optional when editing
            'password'  => [$manager ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'vendor_id' => ['required', 'exists:vendors,id'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        unset($data['password_confirmation']);

        return $data;
    }
}
