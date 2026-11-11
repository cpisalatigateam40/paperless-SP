@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Tambah Master Standar Waterbath</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('master_waterbaths.store') }}" method="POST">
                @include('master_waterbaths._form')
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    // sesuaikan dengan cara init select2 di halaman master lain
    $(function () {
        $('.select2-product').select2({ width: '100%' });
    });
</script>
@endsection