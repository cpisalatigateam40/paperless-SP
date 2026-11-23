@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">Tambah Pemeriksaan Produk Reject Metal Detector</h5>
        </div>
        <div class="card-body">
            @include('report-md-rejects._form', ['action' => route('report_md_rejects.store')])
        </div>
    </div>
</div>
@endsection

@section('script')
    @include('report-md-rejects._script')
@endsection