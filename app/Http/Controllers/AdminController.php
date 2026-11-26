<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        return view('Admin.admin', [
            'users' => User::query()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'student'])],
        ]);

        User::create($validated);

        return to_route('admin.dashboard')->with('success', 'User created.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'student'])],
        ]);

        if ($user->is($request->user()) && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own administrator role.']);
        }

        if (($validated['password'] ?? null) === null) {
            unset($validated['password']);
        }

        if ($user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
        }

        if ($user->isAdmin() && $validated['role'] !== 'admin') {
            return DB::transaction(function () use ($user, $validated) {
                $admins = User::query()->where('role', 'admin')->lockForUpdate()->get(['id']);

                if ($admins->count() <= 1) {
                    return back()->withErrors(['role' => 'You cannot demote the last administrator.']);
                }

                $user->update($validated);

                return to_route('admin.dashboard')->with('success', 'User updated.');
            });
        }

        $user->update($validated);

        return to_route('admin.dashboard')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($user->isAdmin()) {
            return DB::transaction(function () use ($user) {
                $hasAnotherAdmin = User::query()
                    ->where('role', 'admin')
                    ->whereKeyNot($user->getKey())
                    ->lockForUpdate()
                    ->exists();

                if (! $hasAnotherAdmin) {
                    return back()->withErrors(['user' => 'You cannot delete the last administrator.']);
                }

                $user->delete();

                return to_route('admin.dashboard')->with('success', 'User deleted.');
            });
        }

        $user->delete();

        return to_route('admin.dashboard')->with('success', 'User deleted.');
    }
}
