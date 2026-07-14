@extends('layouts.app')

@section('title', 'Tambah Report Smoke House')

@section('content')

<div class="container-xxl flex-grow-1 container-p-y">

    <div class="card">

        <div class="card-header">

            <h5 class="mb-0">

                Tambah Report Smoke House

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