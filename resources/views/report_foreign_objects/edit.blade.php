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
            'label' => $auditMode ? 'Verifikasi Proses Pembuatan Emulsi (Audit)' : 'Verifikasi Proses Pembuatan Emulsi',
            'url'   => $auditMode ? route('report-foreign-objects.audit') : route('report-foreign-objects.index'),
        ],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Edit Foreign Object Contamination Report</h4>
        </div>
        <div class="card-body">
            @include('report_foreign_objects.form')
        </div>
    </div>
</div>
@endsection

@section('script')
    @include('report_foreign_objects._form_scripts')
@endsection