@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail untuk Produk: <strong>{{ $product->product_name }}</strong></h4>
        </div>

        <div class="card-body">
            <form action="{{ route('maurer-standards.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_uuid" value="{{ $product->uuid }}">

                <div class="mb-4">
                    <label>Step Proses</label>
                    <select name="steps[0][process_step_uuid]" class="form-select form-control" required>
                        <option value="">Pilih Step</option>
                        @foreach($steps as $step)
                        <option value="{{ $step->uuid }}">{{ $step->process_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>ST Min (°C)</label>
                        <input type="number" step="0.01" name="steps[0][st_min]" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>ST Max (°C)</label>
                        <input type="number" step="0.01" name="steps[0][st_max]" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Time (min)</label>
                        <input type="number" name="steps[0][time_minute]" class="form-control">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label>RH Min (%)</label>
                        <input type="number" step="0.01" name="steps[0][rh_min]" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>RH Max (%)</label>
                        <input type="number" step="0.01" name="steps[0][rh_max]" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>CT Min (°C)</label>
                        <input type="number" step="0.01" name="steps[0][ct_min]" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>CT Max (°C)</label>
                        <input type="number" step="0.01" name="steps[0][ct_max]" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('maurer-standards.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection