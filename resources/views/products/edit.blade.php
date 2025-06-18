@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Edit Produk</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update', $product->uuid) }}" method="POST">
                @method('PUT')
                @include('products.form')
            </form>
        </div>
    </div>
</div>
@endsection
