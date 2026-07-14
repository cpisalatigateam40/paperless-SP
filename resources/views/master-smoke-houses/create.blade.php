@extends('layouts.app')

@section('title','Tambah Master Smoke House')

@section('content')

<form action="{{ route('master-smoke-houses.store') }}"
    method="POST">

    @csrf

    @include('master-smoke-houses.form')

</form>

@endsection