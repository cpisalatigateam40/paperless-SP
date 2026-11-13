@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Verifikasi Proses Pasteurisasi Produk di Waterbath</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_waterbaths.store') }}" method="POST">
                @include('report_waterbaths.v2._form', ['report' => null, 'formVersion' => $formVersion])
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        $('.select2-product').select2({ width: '100%', placeholder: '-- Pilih Produk --', allowClear: true });
    });
</script>
@include('report_waterbaths.v2._script')
@endsection