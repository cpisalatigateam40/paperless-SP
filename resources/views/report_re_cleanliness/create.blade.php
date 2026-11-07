@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan', 'url' => route('report-re-cleanliness.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header"><h4 class="mb-0">Tambah Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan</h4></div>
        <div class="card-body">
            @include('report_re_cleanliness._form', [
                'action' => route('report-re-cleanliness.store'),
                'method' => 'POST',
                'report' => null,
            ])
        </div>
    </div>
</div>
@endsection