@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Master Data Metal Detector', 'url' => route('metal_detectors.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Tambah Master Data Metal Detector</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('metal_detectors.store') }}">
                @csrf

                <div class="mb-3">
                    <label>Merk</label>
                    <input type="text" name="merk" class="form-control" placeholder="mis: ANRITSU" value="{{ old('merk') }}" required>
                </div>
                <div class="mb-3">
                    <label>Type/Model</label>
                    <input type="text" name="type_model" class="form-control" placeholder="mis: KDU3403NNW" value="{{ old('type_model') }}" required>
                </div>
                <div class="mb-3">
                    <label>No. Series</label>
                    <input type="text" name="no_series" class="form-control" placeholder="mis: 4272220068" value="{{ old('no_series') }}" required>
                </div>

                <a href="{{ route('metal_detectors.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-success">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection