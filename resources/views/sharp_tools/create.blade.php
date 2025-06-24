@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Tambah Benda Tajam</h4>

        </div>
        <div class="card-body">
            <form action="{{ route('sharp_tools.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama Benda Tajam</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Jumlah (Qty)</label>
                    <input type="number" name="quantity" class="form-control"
                        value="{{ old('quantity', $sharpTool->quantity ?? '') }}">
                </div>
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('sharp_tools.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection