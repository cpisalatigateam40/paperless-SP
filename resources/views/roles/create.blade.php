@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="card">
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Tambah Role
        </h5>
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <!-- Role Name Input -->
                <div class="mb-3">
                    <label for="role_name" class="form-label">Nama Role</label>
                    <input
                        type="text"
                        class="form-control @error('role_name') is-invalid @enderror"
                        id="role_name"
                        name="role_name"
                        placeholder="Masukkan Nama Role"
                        value="{{old('role_name')}}"
                        required>
                    @error('role_name')
                    <div class="invalid-feedback">{{$message}}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end mt-5">
                    <button type="submit" class="btn btn-primary">Tambah</button>
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