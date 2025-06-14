@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>{{ isset($thermometer) ? 'Edit' : 'Tambah' }} Thermometer</h5>
        </div>

        <div class="card-body">
            <form action="{{ isset($thermometer) ? route('thermometers.update', $thermometer->uuid) : route('thermometers.store') }}" method="POST">
                @csrf
                @if(isset($thermometer)) @method('PUT') @endif

                <div class="mb-3">
                    <label for="code">Kode Thermometer</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $thermometer->code ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label for="type">Jenis Thermometer</label>
                    <input type="text" name="type" class="form-control" value="{{ old('type', $thermometer->type ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label for="brand">Merek</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand', $thermometer->brand ?? '') }}">
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('thermometers.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection
