@extends('layouts.app')

@section('title', 'Tambah Report Smoke House')

@section('content')

<div class="container-fluid">

    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Proses Pemasakan di Smoke House', 'url' => route('report-smoke-houses.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow">

        <div class="card-header">

            <h5 class="mb-0">

                Tambah Verifikasi Proses Pemasakan di Smoke House

            </h5>

        </div>

        <form action="{{ route('report-smoke-houses.store') }}"
            method="POST">

            @csrf

            @include('report-smoke-houses.form')

        </form>

    </div>

</div>

@endsection