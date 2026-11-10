@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Master Pasteurisasi', 'url' => route('master_pasteurs.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Tambah Master Standar Pasteurisasi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('master_pasteurs.store') }}" method="POST">
                @include('master_pasteurs._form', ['master' => null, 'stepValues' => collect()])
            </form>
        </div>
    </div>
</div>
@endsection