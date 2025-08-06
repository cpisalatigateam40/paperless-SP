@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Standard Fessman</h4>
            <a href="{{ route('fessman-standards.create') }}" class="btn btn-primary btn-sm">+ Tambah Standard</a>
        </div>

        <div class="card-body">
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

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Detail Step</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($standards as $productUuid => $items)
                        @php
                        $product = $items->first()->product;
                        $collapseId = 'collapse' . $no;
                        @endphp
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $product->product_name }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}">
                                    Tampilkan Detail
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="{{ $collapseId }}">
                            <td colspan="3">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>Step</th>
                                            <th>ST (°C)</th>
                                            <th>Time (min)</th>
                                            <th>RH (%)</th>
                                            <th>CT (°C)</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $standard)
                                        <tr class="text-center align-middle">
                                            <td>{{ $standard->processStep->process_name }}</td>
                                            <td>{{ $standard->st_min }} - {{ $standard->st_max }}</td>
                                            <td>{{ $standard->time_minute_min }} - {{ $standard->time_minute_max }}</td>
                                            <td>{{ $standard->rh_min }} - {{ $standard->rh_max }}</td>
                                            <td>{{ $standard->ct_min }} - {{ $standard->ct_max }}</td>
                                            <td>
                                                <a href="{{ route('fessman-standards.edit', $standard->uuid) }}"
                                                    class="btn btn-warning btn-sm">Edit</a>
                                                <form action="{{ route('fessman-standards.destroy', $standard->uuid) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Yakin ingin menghapus?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <a href="{{ route('fessman-standards.add-detail', $product->uuid) }}"
                                    class="btn btn-sm btn-success mt-3">
                                    + Tambah Detail
                                </a>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection