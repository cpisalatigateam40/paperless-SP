@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Area</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('areas.update', $area->uuid) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label>Nama Area</label>
                    <input type="text" name="name" class="form-control" value="{{ $area->name }}" required>
                </div>
                <div class="mt-4">
                    <button class="btn btn-warning">Update</button>
                    <a href="{{ route('areas.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection