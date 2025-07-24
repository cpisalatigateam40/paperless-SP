@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Formula Detail</h4>
        </div>
        <div class="card-body">
            <p> <span style="font-weight: bold;">Nama Formula:</span> {{ $formula->formula_name }}</p>
            <p> <span style="font-weight: bold;">Nama Produk:</span> {{ $formula->product->product_name ?? '-' }}</p>
            <p> <span style="font-weight: bold;">Area:</span> {{ $formula->area->name ?? '-' }}</p>

            <hr>
            <form action="{{ route('formulas.addDetail', $formula->uuid) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Nama Formulasi</label>
                    <input type="text" name="formulation_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Raw Material</label>
                    <select name="raw_material_uuid" class="form-control">
                        <option value="">-- Select Raw Material --</option>
                        @foreach($rawMaterials as $rm)
                        <option value="{{ $rm->uuid }}">{{ $rm->material_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-success">Simpan Formulasi</button>
            </form>

            <h4 class="mt-4">Formulasi</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Formulasi</th>
                        <th>Raw Material</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($formula->formulations as $detail)
                    <tr>
                        <td>{{ $detail->formulation_name }}</td>
                        <td>{{ $detail->rawMaterial->material_name ?? '-' }}</td>
                        <td>
                            <form action="{{ route('formulas.deleteDetail', [$formula->uuid, $detail->uuid]) }}"
                                method="POST">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Delete this detail?')"
                                    class="btn btn-danger btn-sm">Delete</button>
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