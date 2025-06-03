@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Tambah Area</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('areas.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama Area</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('areas.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection