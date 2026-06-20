@extends('layouts.app')

@php
    $isEdit = isset($item);
@endphp

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>{{ $isEdit ? 'Edit Item Checklist' : 'Tambah Item Checklist' }}</h4>
        </div>

        <div class="card-body">
            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                action="{{ $isEdit ? route('master_checklist_items.update', $item->uuid) : route('master_checklist_items.store') }}">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="category" class="form-control" placeholder="Contoh: Packing"
                            value="{{ old('category', $isEdit ? $item->category : null) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Area</label>
                        <select name="area_uuid" class="form-control">
                            <option value="">- Berlaku untuk Semua Area -</option>
                            @foreach($areas as $area)
                            <option value="{{ $area->uuid }}"
                                @selected(old('area_uuid', $isEdit ? $item->area_uuid : null) == $area->uuid)>
                                {{ $area->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Nama Item</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Konveyer filling 1 & meja"
                            value="{{ old('name', $isEdit ? $item->name : null) }}" required>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2" style="gap: .4rem;">
                    <button type="submit" class="btn btn-primary">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Item' }}
                    </button>
                    <a href="{{ route('master_checklist_items.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection