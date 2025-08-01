@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Tambah Premix</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('premixes.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label>Nama Premix</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control" value="{{ old('production_code') }}">
                </div>

                <div class="mb-3">
                    <label>Produsen</label>
                    <input type="text" name="producer" class="form-control" value="{{ old('producer') }}">
                </div>

                <div class="mb-3">
                    <label>Batas Kadaluarsa (Bulan)</label>
                    <input type="number" name="shelf_life" class="form-control" value="{{ old('shelf_life') }}">
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('premixes.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection