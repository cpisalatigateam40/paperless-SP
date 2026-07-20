@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5>Master Data Nomor Form</h5>

            <div class="d-flex">
                @hasanyrole('admin|superadmin')
                <form method="GET" action="{{ route('form-numbers.index') }}" class="mr-3">
                    <input type="hidden" name="section" value="{{ request('section') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">

                    <select name="area"
                            class="form-select form-control form-control"
                            onchange="this.form.submit()">
                        <option value="">Semua Area</option>

                        @foreach($areas as $area)
                            <option value="{{ $area->uuid }}"
                                {{ request('area') == $area->uuid ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endhasanyrole

                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#formNumberModal" onclick="openCreateModal()">
                    Tambah
                </button>
            </div>
            
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Jenis Laporan</th>
                            <th>Nomor Form</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($formNumbers as $fn)
                        <tr>
                            <td>{{ $fn->area->name ?? '-' }}</td>
                            <td>{{ $reportTypes[$fn->report_type] ?? $fn->report_type }}</td>
                            <td>{{ $fn->form_number }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning"
                                    onclick="openEditModal('{{ $fn->id }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('form-numbers.destroy', $fn->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus nomor form ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Belum ada data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="formNumberModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formNumberForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="formNumberModalTitle">Tambah Nomor Form</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jenis Laporan</label>
                        <select name="report_type" id="report_type" class="form-control" required>
                            <option value="">-- Pilih Jenis Laporan --</option>
                            @foreach($reportTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor Form</label>
                        <input type="text" name="form_number" id="form_number" class="form-control" placeholder="Contoh: QM 20 / 01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCreateModal() {
    $('#formNumberModalTitle').text('Tambah Nomor Form');
    $('#formNumberForm').attr('action', "{{ route('form-numbers.store') }}");
    $('#formMethod').val('POST');
    $('#report_type').prop('disabled', false).val('');
    $('#form_number').val('');
}

function openEditModal(id) {
    $.get('/form-numbers/' + id + '/edit', function(data) {
        $('#formNumberModalTitle').text('Edit Nomor Form');
        $('#formNumberForm').attr('action', '/form-numbers/' + id);
        $('#formMethod').val('PUT');
        $('#report_type').val(data.report_type).prop('disabled', true);
        $('#form_number').val(data.form_number);
        $('#formNumberModal').modal('show');
    });
}
</script>
@endpush