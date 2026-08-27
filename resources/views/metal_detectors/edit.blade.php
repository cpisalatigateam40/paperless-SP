@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Master Data Metal Detector', 'url' => route('metal_detectors.index')],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Edit Master Data Metal Detector</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('metal_detectors.update', $metalDetector->uuid) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Merk</label>
                    <input type="text" name="merk" class="form-control" value="{{ old('merk', $metalDetector->merk) }}" required>
                </div>
                <div class="mb-3">
                    <label>Type/Model</label>
                    <input type="text" name="type_model" class="form-control" value="{{ old('type_model', $metalDetector->type_model) }}" required>
                </div>
                <div class="mb-3">
                    <label>No. Series</label>
                    <input type="text" name="no_series" class="form-control" value="{{ old('no_series', $metalDetector->no_series) }}" required>
                </div>
                <!-- <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1"
                        {{ $metalDetector->is_active ? 'checked' : '' }}>
                    <label class="form-check-label">Aktif</label>
                </div> -->

                <a href="{{ route('metal_detectors.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-success">Update</button>
            </form>
        </div>
    </div>
</div>
@endsection