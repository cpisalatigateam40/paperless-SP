@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Edit Barang Mudah Pecah</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('qc-equipment.update', $qcEquipment->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Nama Barang</label>
                    <input type="text" name="item_name" class="form-control" value="{{ $qcEquipment->item_name }}" required>
                </div>

                <div class="mb-3">
                    <label>Nama Area</label>
                    <input type="text" name="section_name" class="form-control" value="{{ $qcEquipment->section_name }}">
                </div>

                <div class="mb-3">
                    <label>Jumlah</label>
                    <input type="number" name="quantity" class="form-control" value="{{ $qcEquipment->quantity }}">
                </div>

                <button class="btn btn-success">Update</button>
                <a href="{{ route('fragile-item.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection
