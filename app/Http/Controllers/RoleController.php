<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Traits\HasPermissionMiddleware;

class RoleController extends Controller
{
    use HasPermissionMiddleware;

    // public function __construct()
    // {
    //     $this->applyPermissionMiddleware('roles');
    // }

    public function index()
    {
        $role = Role::all();
        return view('roles.role', [
            'roles' => $role
        ]);
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:255'
        ]);
        Role::create([
            'name' => $validated['role_name']
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('roles.edit', [
            'role' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'role_name' => 'required|string|max:255',
        ]);

        // Find the role by ID and update it
        $role = Role::findOrFail($id); // Find the role by ID or fail if not found
        $role->update([
            'name' => $validated['role_name'],
        ]);

        // Redirect back to the roles index with a success message
        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        if ($role) {
            return redirect()->back()->with(['success' => 'Data Berhasil Dihapus!']);
        } else {
            return redirect()->back()->with(['error' => 'Data Gagal Dihapus!']);
        }
    }

    public function manageAccess(Role $role)
    {
        $permissions = Permission::all();
        return view('roles.manage', [
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    public function updateAccess(Request $request, Role $role)
    {
        $permissionIds = $request->input('permissions', []);
        $permissionNames = \Spatie\Permission\Models\Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();

        $role->syncPermissions($permissionNames);

        return redirect()->route('roles.index')->with('success', 'Permissions updated successfully');
    }
}