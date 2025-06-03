@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5>Edit Raw Material</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('raw-materials.update', $rawMaterial->uuid) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="material_name" class="form-label">Nama Material</label>
                    <input type="text" name="material_name" id="material_name" class="form-control" value="{{ $rawMaterial->material_name }}" required>
                </div>
                <div class="mb-3">
                    <label for="production_code" class="form-label">Kode Produksi</label>
                    <input type="text" name="production_code" id="production_code" class="form-control" value="{{ $rawMaterial->production_code }}">
                </div>
                <div class="mb-3">
                    <label for="area_uuid" class="form-label">Area</label>
                    <select name="area_uuid" id="area_uuid" class="form-control">
                        <option value="">-- Pilih Area --</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->uuid }}" {{ $area->uuid === $rawMaterial->area_uuid ? 'selected' : '' }}>
                            {{ $area->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('raw-materials.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>


@endsection
