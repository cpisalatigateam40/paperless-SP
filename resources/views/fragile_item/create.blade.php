@extends('layouts.app')

@section('content')
<div class="container">
    <h5>Tambah Barang Mudah Pecah</h5>
    <form action="{{ route('fragile-item.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Nama Barang</label>
            <input type="text" name="item_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nama Area</label>
            <input type="text" name="section_name" class="form-control">
        </div>

        <div class="mb-3">
            <label>Pemilik</label>
            <input type="text" name="owner" class="form-control">
        </div>

        <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" name="quantity" class="form-control">
        </div>

        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('fragile-item.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
