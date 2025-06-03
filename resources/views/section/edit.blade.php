@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <div class="card shadow-mb-4">
        <div class="card-body">
            <form action="{{ route('sections.update', $section->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="section_name" class="form-label">Nama Section</label>
                    <input type="text" class="form-control" id="section_name" name="section_name" value="{{ $section->section_name }}" required>
                </div>

                <div class="mb-3">
                    <label for="area_uuid" class="form-label">Pilih Area</label>
                    <select name="area_uuid" id="area_uuid" class="form-control">
                        <option value="">-- Pilih Area --</option>
                        @foreach($areas as $area)
                        <option value="{{ $area->uuid }}" {{ $section->area_uuid == $area->uuid ? 'selected' : '' }}>
                            {{ $area->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('sections.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
