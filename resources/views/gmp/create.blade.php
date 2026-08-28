@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Penerapan GMP Karyawan & Sanitasi Area', 'url' => route('gmp.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    @include('gmp._form', [
        'gmpHeader' => null,
        'formAction' => route('gmp.store'),
        'formMethod' => 'POST',
        'sections' => $sections,
        'sanitationItemList' => $sanitationItemList,
    ])
</div>
@endsection