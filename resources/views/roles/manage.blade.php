@extends('layouts.app')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <h5 class="card-header">Manage Access for Role: <strong>{{ $role->name }}</strong></h5>

        <div class="card-body">
            <form action="{{ route('roles.manage-access.update', $role->id) }}" method="POST">
                @csrf
                @method('POST')

                <div class="row">
                    @foreach ($permissions as $permission)
                    <div class="col-md-3">
                        <div class="form-check mb-2">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                id="permission_{{ $permission->id }}"
                                class="form-check-input"
                                {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                {{ $permission->name }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end mt-5">
                    <div class="d-flex gap-action">
                        <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary mt-3">Back</a>
                    </div>
                </div>
                
            </form>
        </div>
    </div>
</div>
@endsection