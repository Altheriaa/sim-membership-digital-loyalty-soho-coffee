<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexStaffRequest;
use App\Http\Requests\StaffRequest;
use App\Models\User;
use App\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class StaffController extends Controller
{
    public function index(IndexStaffRequest $request): View
    {
        Gate::authorize('viewAny', User::class);

        $activeRole = $request->role();
        $search = $request->search();

        return view('admin.staff.index', [
            'staff' => User::query()
                ->ofRole($activeRole)
                ->matching($search)
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
            'roles' => Role::cases(),
            'counts' => User::countByRole(),
            'activeRole' => $activeRole,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.staff.form', ['staff' => new User(['is_active' => true])]);
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $staff = new User($request->safe()->only(['name', 'email', 'password', 'role']));
        $staff->is_active = $request->boolean('is_active');
        $staff->save();

        return redirect()->route('admin.staff.index')->with('status', "Akun {$staff->name} dibuat.");
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('admin.staff.form', ['staff' => $user]);
    }

    public function update(StaffRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        if (! $user->is($request->user())) {
            $data['role'] = $request->validated('role');
            $data['is_active'] = $request->boolean('is_active');
        }

        $user->update($data);

        return redirect()->route('admin.staff.index')->with('status', "Akun {$user->name} diperbarui.");
    }
}
