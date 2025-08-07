@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header">
            <h4>Edit Standard Maurer</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('maurer-standards.update', $standard->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="product_uuid" class="form-label">Produk</label>
                    <select name="product_uuid" id="product_uuid" class="form-select form-control" required>
                        <option value="">Pilih Produk</option>
                        @foreach($products as $product)
                        <option value="{{ $product->uuid }}"
                            {{ old('product_uuid', $standard->product_uuid) == $product->uuid ? 'selected' : '' }}>
                            {{ $product->product_name }} {{ $product->nett_weight }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="process_step_uuid" class="form-label">Step Proses</label>
                    <select name="process_step_uuid" id="process_step_uuid" class="form-select form-control" required>
                        <option value="">Pilih Step</option>
                        @foreach($steps as $step)
                        <option value="{{ $step->uuid }}"
                            {{ old('process_step_uuid', $standard->process_step_uuid) == $step->uuid ? 'selected' : '' }}>
                            {{ $step->process_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">ST Min (°C)</label>
                        <input type="number" step="0.01" name="st_min" class="form-control"
                            value="{{ old('st_min', $standard->st_min) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">ST Max (°C)</label>
                        <input type="number" step="0.01" name="st_max" class="form-control"
                            value="{{ old('st_max', $standard->st_max) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Time (menit)</label>
                        <input type="number" name="time_minute" class="form-control"
                            value="{{ old('time_minute', $standard->time_minute) }}">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">RH Min (%)</label>
                        <input type="number" step="0.01" name="rh_min" class="form-control"
                            value="{{ old('rh_min', $standard->rh_min) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">RH Max (%)</label>
                        <input type="number" step="0.01" name="rh_max" class="form-control"
                            value="{{ old('rh_max', $standard->rh_max) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">CT Min (°C)</label>
                        <input type="number" step="0.01" name="ct_min" class="form-control"
                            value="{{ old('ct_min', $standard->ct_min) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">CT Max (°C)</label>
                        <input type="number" step="0.01" name="ct_max" class="form-control"
                            value="{{ old('ct_max', $standard->ct_max) }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('maurer-standards.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection