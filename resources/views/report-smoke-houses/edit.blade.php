@extends('layouts.app')

@php
    $auditMode = $report->is_audit || request()->boolean('audit');
@endphp

@section('title', 'Edit Report Smoke House')

@section('content')

<div class="container-fluid">
    @if ($report->is_audit)
        <x-audit-banner />
    @endif
    
    <x-breadcrumb :items="[
        [
            'label' => $auditMode ? 'Verifikasi Proses Pemasakan di Smoke House (Audit)' : 'Verifikasi Proses Pemasakan di Smoke House',
            'url'   => $auditMode ? route('report-smoke-houses.audit') : route('report-smoke-houses.index'),
        ],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow">

        <div class="card-header">

            <h5 class="mb-0">

                Edit Verifikasi Proses Pemasakan di Smoke House

            </h5>

        </div>

        <form
            action="{{ route('report-smoke-houses.update',$report->uuid) }}"
            method="POST">

            @csrf
            @method('PUT')

            @include('report-smoke-houses.form')

        </form>

    </div>

</div>

@endsection