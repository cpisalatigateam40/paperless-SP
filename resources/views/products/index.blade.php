@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>List Produk</h5>
            <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">+ Tambah Produk</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                @if(session('success'))
                    <div id="success-alert" class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div id="error-alert" class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Produk</th>
                            <th>Merek</th>
                            <th>Berat (Gram)</th>
                            <th>Kadaluwarsa (Bulan)</th>
                            <th>Area</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->product_name }}</td>
                            <td>{{ $p->brand }}</td>
                            <td>{{ $p->nett_weight }}</td>
                            <td>{{ $p->shelf_life }}</td>
                            <td>{{ $p->area->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('products.edit', $p->uuid) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('products.destroy', $p->uuid) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete product?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 1rem;">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            setTimeout(() => {
                $('#success-alert').fadeOut('slow');
                $('#error-alert').fadeOut('slow');
            }, 3000);
        });
    </script>
@endsection
