@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Standard Stuffers</h4>
            <a href="{{ route('standard-stuffers.create') }}" class="btn btn-primary btn-sm">Tambah Data</a>
        </div>

        <div class="card-body">
            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Area</th>
                        <th>Long (mm)</th>
                        <th>Diameter (mm)</th>
                        <th>Weight per 3 pcs (gr)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stuffers as $stuffer)
                    <tr>
                        <td>{{ $stuffer->product->product_name ?? '-' }}</td>
                        <td>{{ $stuffer->area->name ?? '-' }}</td>
                        <td>{{ $stuffer->long_min }} - {{ $stuffer->long_max }}</td>
                        <td>{{ $stuffer->diameter }}</td>
                        <td>{{ $stuffer->weight_min }} - {{ $stuffer->weight_max }}</td>
                        <td>
                            <a href="{{ route('standard-stuffers.edit', $stuffer->uuid) }}"
                                class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('standard-stuffers.destroy', $stuffer->uuid) }}" method="POST"
                                style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus data ini?')"
                                    class="btn btn-sm btn-danger">Hapus</button>
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