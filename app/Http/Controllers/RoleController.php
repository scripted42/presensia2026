<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });
        
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Enforce reserved role slugs
        $reserved = ['admin','teacher','tu','bk','headmaster','kesiswaan','student','super-admin'];
        if (in_array(strtolower($request->name), $reserved, true)) {
            return redirect()->back()->withErrors(['name' => 'Slug role ini adalah peran sistem dan tidak boleh dibuat ulang.']);
        }

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'display_name' => $request->display_name,
        ]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });
        
        return view('admin.roles.show', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'display_name' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Ignore any attempt to change slug name (even via inspector)
        $role->update([
            'display_name' => $request->display_name,
        ]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        $systemRoles = ['admin', 'headmaster', 'teacher', 'tu', 'bk', 'kesiswaan', 'student'];
        
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Role sistem tidak dapat dihapus.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }

    /**
     * Assign role to user.
     */
    public function assignToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = \App\Models\User::where('school_id', auth()->user()->school_id)->findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        $user->assignRole($role);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role {$role->name} berhasil diberikan ke {$user->name}.");
    }

    /**
     * Remove role from user.
     */
    public function removeFromUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = \App\Models\User::where('school_id', auth()->user()->school_id)->findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        $user->removeRole($role);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role {$role->name} berhasil dihapus dari {$user->name}.");
    }
}