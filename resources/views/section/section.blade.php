@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Data Section</h5>

            <div class="d-flex align-items-center gap-2" style="gap: .5rem;">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Cari Section"
                        style="border-radius: 0;">
                    <span class="input-group-text" style="border-radius: 0;">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <a href="{{ route('sections.create') }}" class="btn btn-primary btn-sm">Tambah Section</a></a>
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
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No.</th>
                            <th>Section</th>
                            <th>Area</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sections as $section)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $section->section_name }}</td>
                            <td>{{ $section->area->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('sections.edit', $section->uuid) }}"
                                    class="btn btn-warning btn-sm">Edit</a>
                                <form action="{{ route('sections.destroy', $section->uuid) }}" method="POST"
                                    style="display:inline-block;"
                                    onsubmit="return confirm('Yakin ingin menghapus section ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

                <div class="mt-3">
                    {{ $sections->links('pagination::bootstrap-5') }}
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