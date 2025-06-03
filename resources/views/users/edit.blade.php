@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit User</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('users.update', $user->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" name="username" value="{{ old('username', $user->username) }}">
                </div>

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password">
                </div>

                <div class="form-group">
                    <label>Area</label>
                    <select name="area_uuid" class="form-control">
                        @foreach($areas as $area)
                            <option value="{{ $area->uuid }}" {{ $area->uuid == old('area_uuid', $user->area_uuid) ? 'selected' : '' }}>{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-end mt-5">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
