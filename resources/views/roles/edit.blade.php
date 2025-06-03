@extends('layouts.app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Edit Role
        </h5>
        <div class="card-body">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                <!-- Role Name Input -->
                <div class="mb-3">
                    <label for="role_name" class="form-label">Nama Role</label>
                    <input
                        type="text"
                        class="form-control @error('role_name') is-invalid @enderror"
                        id="role_name"
                        name="role_name"
                        placeholder="Masukkan Nama Role"
                        value="{{old('role_name', $role->name)}}"
                        required>
                    @error('role_name')
                    <div class="invalid-feedback">{{$message}}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end mt-5">
                    <div>
                        <button type="submit" class="btn btn-primary">Edit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
</script>
@endsection