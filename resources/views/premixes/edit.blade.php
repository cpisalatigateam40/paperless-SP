@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Edit Premix</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('premixes.update', $premix->uuid) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label>Nama Premix</label>
                    <input type="text" name="name" class="form-control" value="{{ $premix->name }}" required>
                </div>

                <!-- <div class="mb-3">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control"
                        value="{{ $premix->production_code }}" required>
                </div> -->

                <div class="mb-3">
                    <label>Produsen</label>
                    <input type="text" name="producer" class="form-control" value="{{ $premix->producer }}">
                </div>

                <div class="mb-3">
                    <label>Shelf Life (hari)</label>
                    <input type="text" name="shelf_life" class="form-control" value="{{ $premix->shelf_life }}">
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('premixes.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection