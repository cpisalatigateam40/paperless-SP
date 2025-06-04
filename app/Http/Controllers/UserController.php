<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['area'])->get();
        return view('users.user', compact('users'));
    }

    public function create()
    {
        $areas = Area::all();
        return view('users.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'area_uuid' => 'required|exists:areas,uuid',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'uuid' => Str::uuid(),
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'area_uuid' => $validated['area_uuid'],
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function destroy($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function edit($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $areas = Area::all();
        $roles = \Spatie\Permission\Models\Role::all();

        return view('users.edit', compact('user', 'areas', 'roles'));
    }

    public function update(Request $request, $uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'username' => 'required|unique:users,username,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'area_uuid' => 'required|exists:areas,uuid',
            'role' => 'required|exists:roles,name',
        ]);

        $user->username = $validated['username'];
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->area_uuid = $validated['area_uuid'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Update roles: syncRoles akan hapus role lama dan assign role baru
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
    }
}