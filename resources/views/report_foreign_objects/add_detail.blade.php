@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Pemeriksaan Kontaminasi Benda Asing', 'url' => route('report-foreign-objects.index')],
        ['label' => 'Tambah Detail', 'url' => null],
    ]" />

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('report-foreign-objects.store-detail', $report->uuid) }}" method="POST"
        enctype="multipart/form-data" id="foreign-object-form">
        @csrf

        <div class="card shadow mb-3">
            <div class="card-header">
                <h5 class="mb-0">Tambah Detail Pemeriksaan Kontaminasi Benda Asing</h5>
            </div>
            <div class="card-body">
                <div id="details-wrapper">
                    @include('report_foreign_objects._detail_fields', ['index' => 0, 'detail' => null])
                </div>

                <template id="detail-template">
                    @include('report_foreign_objects._detail_fields', ['index' => '__INDEX__', 'detail' => null])
                </template>

                <div class="d-flex mt-3" style="gap: .5rem;">
                    <button type="button" class="btn btn-primary" onclick="addDetail()">+ Tambah Detail</button>
                    <a href="{{ route('report-foreign-objects.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
    @include('report_foreign_objects._form_scripts')
@endsection