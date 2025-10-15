<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::with('roles')->get()->groupBy(function($permission) {
            return explode('-', $permission->name)[0];
        });
        
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $permissionGroups = [
            'dashboard' => 'Dashboard',
            'user' => 'Manajemen User',
            'student' => 'Manajemen Siswa',
            'class' => 'Manajemen Kelas',
            'attendance' => 'Absensi',
            'report' => 'Laporan',
            'leave' => 'Izin & Cuti',
            'setting' => 'Pengaturan',
            'bulk' => 'Operasi Massal',
        ];
        
        return view('admin.permissions.create', compact('roles', 'permissionGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->has('roles')) {
            $roles = Role::whereIn('id', $request->roles)->get();
            $permission->syncRoles($roles);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');
        $roles = Role::all();
        
        return view('admin.permissions.show', compact('permission', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $permission->load('roles');
        $roles = Role::all();
        $permissionGroups = [
            'dashboard' => 'Dashboard',
            'user' => 'Manajemen User',
            'student' => 'Manajemen Siswa',
            'class' => 'Manajemen Kelas',
            'attendance' => 'Absensi',
            'report' => 'Laporan',
            'leave' => 'Izin & Cuti',
            'setting' => 'Pengaturan',
            'bulk' => 'Operasi Massal',
        ];
        
        return view('admin.permissions.edit', compact('permission', 'roles', 'permissionGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'group' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission->update([
            'name' => $request->name,
        ]);

        if ($request->has('roles')) {
            $roles = Role::whereIn('id', $request->roles)->get();
            $permission->syncRoles($roles);
        } else {
            $permission->syncRoles([]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        // Prevent deletion of system permissions
        $systemPermissions = [
            'view-dashboard', 'view-users', 'create-users', 'edit-users', 'delete-users',
            'view-students', 'create-students', 'edit-students', 'delete-students',
            'view-classes', 'create-classes', 'edit-classes', 'delete-classes',
            'view-attendance', 'create-attendance', 'edit-attendance', 'delete-attendance',
            'view-own-attendance', 'create-own-attendance',
            'view-student-attendance', 'create-student-attendance',
            'view-reports', 'export-reports',
            'view-leaves', 'create-leaves', 'edit-leaves', 'approve-leaves',
            'view-own-leaves', 'create-own-leaves',
            'view-settings', 'edit-settings',
            'bulk-import-users', 'bulk-import-students',
        ];
        
        if (in_array($permission->name, $systemPermissions)) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Permission sistem tidak dapat dihapus.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission berhasil dihapus.');
    }
}