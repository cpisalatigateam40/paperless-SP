

@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5> Laporan Verifikasi Kebersihan Ruang Penyimpanan</h5>
            
            <div class="d-flex gap-2" style="gap: .4rem;">

                {{-- 🔍 SEARCH --}}
                <form method="GET"
                    action="{{ route('cleanliness.index') }}"
                    class="d-flex align-items-center"
                    style="gap: .4rem;">

                    {{-- pertahankan filter section --}}
                    <input type="hidden" name="section" value="{{ request('section') }}">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari laporan..."
                        value="{{ request('search') }}"
                    >

                    {{-- 🔍 BUTTON CARI --}}
                    <button type="submit" class="btn btn-outline-primary">
                        Cari
                    </button>

                    {{-- 🔄 RESET --}}
                    @if(request('search') || request('section'))
                        <a href="{{ route('cleanliness.index') }}"
                        class="btn btn-danger"
                        title="Reset Filter">
                            Reset
                        </a>
                    @endif

                </form>

                <a href="{{ route('cleanliness.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                @if(session('success'))
                <div id="success-alert" class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div id="error-alert" class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @php
                $roomNames = $reports->pluck('room_name')->unique()->filter()->values();
                @endphp

                <ul class="nav nav-tabs mb-3" id="roomTabs" role="tablist">
                    @foreach($roomNames as $index => $room)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}" id="{{ Str::slug($room) }}-tab"
                            data-bs-toggle="tab" data-bs-target="#{{ Str::slug($room) }}" type="button" role="tab">
                            {{ $room }}
                        </button>
                    </li>
                    @endforeach
                </ul>

                <div class="tab-content" id="roomTabsContent">
                    @foreach($roomNames as $index => $room)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ Str::slug($room) }}"
                        role="tabpanel" aria-labelledby="{{ Str::slug($room) }}-tab">

                        @include('cleanliness._report-table', [
                        'filteredReports' => $reports->where('room_name', $room)
                        ])
                    </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $reports->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection