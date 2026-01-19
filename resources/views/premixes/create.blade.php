@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Tambah Bahan penunjang & Premix</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('premixes.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label>Nama Bahan penunjang & Premix</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label>Produsen</label>
                    <input type="text" name="producer" class="form-control" value="{{ old('producer') }}">
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Best Before</label>
                        <input type="text" name="shelf_life" class="form-control" value="{{ old('shelf_life') }}">
                    </div>

                    <div class="mb-3 col-md-6">
                        <label>Satuan</label>
                        <select name="unit" class="form-control">
                            <option value="">-- Pilih Satuan --</option>
                            <option value="Jam" {{ old('unit') == 'Jam' ? 'selected' : '' }}>Jam</option>
                            <option value="Hari" {{ old('unit') == 'Hari' ? 'selected' : '' }}>Hari</option>
                            <option value="Bulan" {{ old('unit') == 'Bulan' ? 'selected' : '' }}>Bulan</option>
                            <option value="Tahun" {{ old('unit') == 'Tahun' ? 'selected' : '' }}>Tahun</option>
                        </select>
                    </div>
                </div>

                


                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('premixes.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection