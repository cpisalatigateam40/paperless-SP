@extends('layouts.app')

@php
    $auditMode = $report->is_audit || request()->boolean('audit');
@endphp

@section('content')
<div class="container-fluid">
    @if ($report->is_audit)
        <x-audit-banner />
    @endif

    <x-breadcrumb :items="[
        [
            'label' => $auditMode
                ? 'Verifikasi Proses Pasteurisasi Produk di Retort Chamber (Audit)'
                : 'Verifikasi Proses Pasteurisasi Produk di Retort Chamber',
            'url'   => $auditMode ? route('report_pasteurs.audit') : route('report_pasteurs.index'),
        ],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Edit Verifikasi Proses Pasteurisasi Produk di Retort Chamber</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_pasteurs.update', $report->uuid) }}" method="POST" id="pasteur-form">
                @method('PUT')
                @include('report_pasteurs._form', ['report' => $report])
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
    @include('report_pasteurs._form_scripts')
@endsection