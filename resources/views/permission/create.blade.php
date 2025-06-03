@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4">
        
        <h5 class="card-header d-flex justify-content-between align-items-center">
            Tambah Permission
        </h5>

        <div class="card-body">
            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Permission Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="d-flex justify-content-end mt-5">
                    <div>
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>

                </div>
                
            </form>
        </div>
    </div>
</div>
@endsection