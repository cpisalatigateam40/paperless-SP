@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Tambah Raw Material</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('raw-materials.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="material_name" class="form-label">Nama Material</label>
                    <input type="text" name="material_name" id="material_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="supplier" class="form-label">Produsen</label>
                    <input type="text" name="supplier" id="supplier" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="shelf_life" class="form-label">Batas Kadaluarsa (Bulan)</label>
                    <input type="number" name="shelf_life" id="shelf_life" class="form-control" min="0">
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('raw-materials.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>


@endsection