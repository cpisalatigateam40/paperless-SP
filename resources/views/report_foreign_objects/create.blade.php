@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Kontaminasi Benda Asing', 'url' => route('report-foreign-objects.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0">Tambah Foreign Object Contamination Report</h4>
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