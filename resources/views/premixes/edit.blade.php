@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Edit Bahan penunjang & Premix</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('premixes.update', $premix->uuid) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label>Nama Bahan penunjang & Premix</label>
                    <input type="text" name="name" class="form-control" value="{{ $premix->name }}" required>
                </div>

                <div class="mb-3">
                    <label>Produsen</label>
                    <input type="text" name="producer" class="form-control" value="{{ $premix->producer }}">
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label>Best Before</label>
                        <input type="text" name="shelf_life" class="form-control" value="{{ $premix->shelf_life }}">
                    </div>

                    <div class="mb-3 col-md-6">
                        <label>Satuan</label>
                        <select name="unit" class="form-control">
                            <option value="">-- Pilih Satuan --</option>
                            <option value="Jam" {{ old('unit', $premix->unit) == 'Jam' ? 'selected' : '' }}>Jam</option>
                            <option value="Hari" {{ old('unit', $premix->unit) == 'Hari' ? 'selected' : '' }}>Hari</option>
                            <option value="Bulan" {{ old('unit', $premix->unit) == 'Bulan' ? 'selected' : '' }}>Bulan</option>
                            <option value="Tahun" {{ old('unit', $premix->unit) == 'Tahun' ? 'selected' : '' }}>Tahun</option>
                        </select>
                    </div>
                </div>

                


                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('premixes.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection