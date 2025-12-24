@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Master Data Ruangan, Mesin, dan Peralatan</h4>

            <div class="d-flex align-items-center gap-2" style="gap: .5rem;">
                <form method="GET" action="{{ route('rooms.index') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-auto p-0">
                            <input type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control form-control-sm"
                                placeholder="Cari ruangan, elemen, mesin, atau part">
                        </div>

                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm">
                                Search
                            </button>

                            @if(request('search'))
                                <a href="{{ route('rooms.index') }}"
                                class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <ul class="nav nav-tabs" id="masterTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="room-tab" data-bs-toggle="tab" href="#room" role="tab">Ruangan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="equipment-tab" data-bs-toggle="tab" href="#equipment" role="tab">Mesin &
                        Peralatan</a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="masterTabContent">
                {{-- Tab Ruangan --}}
                <div class="tab-pane fade show active" id="room" role="tabpanel">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-4 mt-4" style="gap: .4rem;">
                        <a href="{{ route('rooms.template') }}" class="btn btn-outline-success btn-sm">
                            Download Template
                        </a>

                        <form action="{{ route('rooms.import') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="d-flex align-items-center gap-2">
                            @csrf

                            <input type="file"
                                name="file"
                                accept=".xlsx,.xls"
                                class="form-control form-control-sm mr-2"
                                style="max-width: 220px"
                                required>

                            <button type="submit" class="btn btn-success btn-sm">
                                Import Excel
                            </button>
                        </form>
                    </div>
                    <form action="{{ route('rooms.store') }}" method="POST" class="mb-4">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <label for="roomName">Nama Ruangan</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="elements">Elemen (pisahkan dengan koma)</label>
                                <input type="text" name="elements" class="form-control"
                                    placeholder="Contoh: Dinding, Lantai, Langit-langit">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 mb-5">Simpan Ruangan</button>

                        
                    </form>

                    <h5>Daftar Ruangan</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Area</th>
                                <th>Elemen</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rooms as $room)
                            <tr>
                                <td>{{ $room->name }}</td>
                                <td>{{ optional($room->area)->name }}</td>
                                <td>
                                    <ul>
                                        @foreach($room->elements as $el)
                                        <li>{{ $el->element_name }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <form action="{{ route('rooms.destroy', $room->uuid) }}" method="POST"
                                        onsubmit="return confirm('Hapus ruangan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Tab Mesin/Peralatan --}}
                <div class="tab-pane fade" id="equipment" role="tabpanel">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-4 mt-4" style="gap: .4rem;">
                        <a href="{{ route('equipments.template') }}" class="btn btn-outline-success btn-sm">
                            Download Template
                        </a>

                        <form action="{{ route('equipments.import') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="d-flex align-items-center gap-2">
                            @csrf

                            <input type="file"
                                name="file"
                                accept=".xlsx,.xls"
                                class="form-control form-control-sm mr-2"
                                style="max-width: 220px"
                                required>

                            <button type="submit" class="btn btn-success btn-sm">
                                Import Excel
                            </button>
                        </form>
                    </div>
                    <form action="{{ route('equipments.store') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <label for="equipmentName">Nama Mesin/Peralatan</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="parts">Part (pisahkan dengan koma)</label>
                                <input type="text" name="parts" class="form-control"
                                    placeholder="Contoh: Screw, Panel, Cover">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3 mb-5">Simpan Mesin/Peralatan</button>
                    </form>

                    <h5>Daftar Mesin / Peralatan</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Area</th>
                                <th>Parts</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($equipments as $eq)
                            <tr>
                                <td>{{ $eq->name }}</td>
                                <td>{{ optional($eq->area)->name }}</td>
                                <td>
                                    <ul>
                                        @foreach($eq->parts as $part)
                                        <li>{{ $part->part_name }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <form action="{{ route('equipments.destroy', $eq->uuid) }}" method="POST"
                                        onsubmit="return confirm('Hapus peralatan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript bantuan --}}

@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});

function isSimilar(a, b) {
    return a.includes(b) || b.includes(a);
}

$(document).ready(function() {
    $('#searchInput').on('keyup', function() {
        let keyword = $(this).val().toLowerCase();
        $('table tbody tr').each(function() {
            let rowText = $(this).text().toLowerCase();
            let isMatch = isSimilar(rowText, keyword);
            $(this).toggle(isMatch);
        });
    });
});
</script>
@endsection