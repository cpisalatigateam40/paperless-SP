@extends('layouts.app')

@php
    $auditMode = $report->is_audit || request()->boolean('audit');
@endphp

@section('content')
<div class="container-fluid {{ $auditMode ? 'wb-audit-mode' : '' }}">
    @if ($report->is_audit)
        <x-audit-banner />
    @endif

    <x-breadcrumb :items="[
        [
            'label' => $auditMode
                ? 'Verifikasi Proses Pasteurisasi Produk di Waterbath (Audit)'
                : 'Verifikasi Proses Pasteurisasi Produk di Waterbath',
            'url'   => $auditMode ? route('report_waterbaths.audit') : route('report_waterbaths.index'),
        ],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Edit Verifikasi Proses Pasteurisasi Produk di Waterbath</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_waterbaths.update', $report->uuid) }}" method="POST">
                @method('PUT')
                @include('report_waterbaths.v2._form', ['report' => $report, 'auditMode' => $auditMode])
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