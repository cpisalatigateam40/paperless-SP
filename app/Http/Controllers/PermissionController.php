<?php

namespace App\Http\Controllers;

use App\Traits\HasPermissionMiddleware;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use HasPermissionMiddleware;

    // public function __construct()
    // {
    //     $this->applyPermissionMiddleware('permissions');
    // }

    public function index()
    {
        $permission = Permission::all();
        return view('permission.permission', [
            'permissions' => $permission
        ]);
    }

    public function create()
    {
        return view('permission.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name'
        ]);

        Permission::create([
            'name' => $request->name
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission Created Successfully');
    }

    public function edit($id)
    {
        $permission = Permission::findById($id);
        return view('permission.edit', [
            'permission' => $permission
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update([
            'name' => $request->name,
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->roles()->detach();
            $permission->delete();

            return redirect()->back()->with('success', 'Permission deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus permission: ' . $e->getMessage());
        }
    }
}