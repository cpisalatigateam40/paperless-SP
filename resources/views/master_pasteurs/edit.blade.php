@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Master Pasteurisasi', 'url' => route('master_pasteurs.index')],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Edit Master Standar Pasteurisasi</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('master_pasteurs.update', $master->uuid) }}" method="POST">
                @method('PUT')
                @include('master_pasteurs._form', ['products' => collect()])
            </form>
        </div>
    </div>
</div>
@endsection