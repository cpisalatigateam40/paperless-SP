@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Data Formula</h4>
            <a href="{{ route('formulas.create') }}" class="btn btn-primary btn-sm">Tambah Formula</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Formula</th>
                        <th>Produk</th>
                        <th>Area</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formulas as $formula)
                    <tr>
                        <td>{{ $formula->formula_name }}</td>
                        <td>{{ $formula->product->product_name ?? '-' }}</td>
                        <td>{{ $formula->area->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('formulas.detail', $formula->uuid) }}" class="btn btn-info btn-sm">Lihat
                                Formula</a>
                            <form action="{{ route('formulas.destroy', $formula->uuid) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Delete formula?')"
                                    class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection