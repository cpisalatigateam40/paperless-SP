@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Tambah Inventaris Peralatan QC</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('qc-equipment.store') }}" method="POST">
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
                    <label>Jumlah</label>
                    <input type="number" name="quantity" class="form-control">
                </div>

                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('qc-equipment.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection
