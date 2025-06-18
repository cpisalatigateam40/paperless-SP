@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Tambah Produk</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
                @include('products.form')
            </form>
        </div>
    </div>
</div>
@endsection
