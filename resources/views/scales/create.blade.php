@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Tambah Timbangan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('scales.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="code" class="form-label">Kode Timbangan</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
                </div>

                <div class="mb-3">
                    <label for="type" class="form-label">Jenis Timbangan</label>
                    <input type="text" name="type" class="form-control" value="{{ old('type') }}" required>
                </div>

                <div class="mb-3">
                    <label for="brand" class="form-label">Merek Timbangan</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand') }}" required>
                </div>

                <div class="mb-3">
                    <label for="owner" class="form-label">Pemilik</label>
                    <input type="text" name="owner" class="form-control" value="{{ old('owner') }}">
                </div>

                <button class="btn btn-success">Simpan</button>
                <a href="{{ route('scales.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection