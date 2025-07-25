@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Buat Formula</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('formulas.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama Formula</label>
                    <input type="text" name="formula_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Nama Produk</label>
                    <select name="product_uuid" class="form-control">
                        <option value="">-- Select Product --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-success">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection