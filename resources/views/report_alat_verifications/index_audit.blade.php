@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-user-shield mr-1"></i>
                Verifikasi Alat Ukur (Data Audit)
            </h5>

            <div class="d-flex" style="gap: .4rem;">
                {{-- SEARCH --}}
                <form method="GET" action="{{ route('report-alat-verifications.audit') }}"
                    class="d-flex align-items-center" style="gap: .4rem;">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari shift, pembuat..." value="{{ request('search') }}">

                    <select name="per_page" class="form-select form-control" onchange="this.form.submit()">
                        @foreach([5, 10, 25] as $n)
                            <option value="{{ $n }}" {{ request('per_page', 5) == $n ? 'selected' : '' }}>
                                {{ $n }} / halaman
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-outline-primary">Cari</button>

                    @if(request('search') || request('per_page'))
                        <a href="{{ route('report-alat-verifications.audit') }}" class="btn btn-danger">Reset</a>
                    @endif
                </form>

                @hasanyrole('admin|superadmin|SPV QC')
                <a href="{{ route('report-alat-verifications.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Data Operasional
                </a>
                @endhasanyrole
            </div>
        </div>

        <div class="card-body" style="padding-top: 1rem !important;">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div id="info-alert" class="alert alert-info">{{ session('info') }}</div>
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


            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Area</th>
                            <th>Dibuat Oleh</th>
                            <th>Jumlah Alat</th>
                            <th style="width:30%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $report)
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ \Carbon\Carbon::parse($report->date)->translatedFormat('d M Y') }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->created_by }}
                                @if($report->is_audit)
                                    <span class="badge bg-info" style="color: white;">Audit</span>
                                @endif
                            </td>
                            <td>{{ $report->details->count() }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="collapse"
                                    data-bs-target="#detail-{{ $report->uuid }}" aria-expanded="false" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                {{-- Edit: baris audit langsung edit, baris operasional disalin dulu --}}
                                @can('edit report')
                                <a href="{{ $report->is_audit
                                        ? route('report-alat-verifications.edit', $report->uuid)
                                        : route('report-alat-verifications.copy-to-audit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning"
                                    title="{{ $report->is_audit ? 'Edit Data Audit' : 'Salin & Edit Data Audit' }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Hapus: hanya salinan audit --}}
                                @if($report->is_audit)
                                    @can('delete report')
                                    <form action="{{ route('report-alat-verifications.destroy', $report->uuid) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus data audit ini beserta seluruh detail alatnya?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif

                                {{-- Known --}}
                                @can('known report')
                                    @if(!$report->known_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report-alat-verifications.known', $report->uuid) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @else
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $report->known_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($report->known_by)
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $report->known_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Approve --}}
                                @can('approve report')
                                    @if(!$report->approved_by)
                                        @if($report->is_audit)
                                        <form action="{{ route('report-alat-verifications.approve', $report->uuid) }}" method="POST"
                                            style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-thumbs-up"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @else
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $report->approved_by }}
                                        </span>
                                    @endif
                                @else
                                    @if($report->approved_by)
                                        <span class="badge bg-success"
                                            style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                            <i class="fas fa-check"></i> {{ $report->approved_by }}
                                        </span>
                                    @endif
                                @endcan

                                {{-- Export PDF --}}
                                <a href="{{ route('report-alat-verifications.export-pdf', $report->uuid) }}"
                                    class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>

                                {{-- Dropdown audit (gear) --}}
                                @hasanyrole('admin|superadmin|SPV QC')
                                <x-audit-dropdown :item="$report" route-prefix="report-alat-verifications" />
                                @endhasanyrole
                            </td>
                        </tr>

                        <tr class="collapse" id="detail-{{ $report->uuid }}">
                            <td colspan="7" class="p-0 border-0">
                                <div class="p-3 bg-light">
                                    @if($report->details->isEmpty())
                                        <span class="text-muted">Belum ada detail alat.</span>
                                    @else
                                        <table class="table table-sm table-borderless mb-0">
                                            <thead>
                                                <tr class="border-bottom">
                                                    <th>Jenis & Kode Alat</th>
                                                    <th>Titik Ukur</th>
                                                    <th>Nilai Baca (kg/°C)</th>
                                                    <th>Jam Pemeriksaan</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report->details as $detail)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-{{ $detail->alat_type === 'scale' ? 'primary' : 'info' }}"
                                                            style="color: white;">
                                                            {{ $detail->alat_type === 'scale' ? 'Timbangan' : 'Thermometer' }}
                                                        </span>
                                                        {{ $detail->alat->type ?? '-' }} - {{ $detail->alat->code ?? '-' }}
                                                    </td>
                                                    <td>{{ $detail->titik_ukur }}</td>
                                                    <td>{{ $detail->nilai_baca }}</td>
                                                    <td>{{ $detail->check_time ? \Carbon\Carbon::parse($detail->check_time)->format('H:i') : '-' }}</td>
                                                    <td>{{ $detail->notes ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif

                                    {{-- Tambah detail: hanya salinan audit --}}
                                    @if($report->is_audit)
                                        @can('create report')
                                        <button type="button" class="btn btn-sm btn-outline-primary mt-3"
                                            onclick="openAddDetailModal('{{ $report->uuid }}')">
                                            + Tambah Detail
                                        </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data verifikasi alat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $records->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah Detail --}}
<div class="modal fade" id="addDetailModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="addDetailForm" method="POST" action="">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Tambah Detail Alat (Data Audit)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          @error('items') <div class="alert alert-danger">{{ $message }}</div> @enderror

          <div class="mb-2">
              <label>Jam Pemeriksaan (global)</label>
              <input type="time" id="modal-global-check-time" class="form-control" style="max-width:200px" value="08:00">
          </div>

          <table class="table table-bordered align-middle">
              <thead>
                  <tr>
                      <th style="width:5%">No</th>
                      <th style="width:25%">Jenis & Kode Alat</th>
                      <th style="width:20%">Titik Ukur</th>
                      <th style="width:15%">Nilai Baca (kg/°C)</th>
                      <th style="width:15%">Jam Pemeriksaan</th>
                      <th style="width:15%">Catatan</th>
                      <th style="width:5%"></th>
                  </tr>
              </thead>
              <tbody id="modal-detail-body"></tbody>
          </table>

          <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="modal-add-row">+ Tambah Baris</button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('script')
@php
    // auditIndex tidak mengirim data master alat, jadi diambil di sini
    $scaleOptions = \App\Models\Scale::all()->map(fn($s) => ['uuid' => $s->uuid, 'label' => $s->type . ' - ' . $s->code]);
    $thermometerOptions = \App\Models\Thermometer::all()->map(fn($t) => ['uuid' => $t->uuid, 'label' => $t->type . ' - ' . $t->code]);
@endphp
<script>
$(document).ready(function () {
    setTimeout(() => {
        $('#success-alert, #info-alert, #error-alert').fadeOut('slow');
    }, 3000);
});

const scales = @json($scaleOptions);
const thermometers = @json($thermometerOptions);

const titikUkurOptions = {
    scale: ['100 Gr','200 Gr','500 Gr','1000 Gr','2000 Gr','5000 Gr','10000 Gr','15000 Gr','20000 Gr','25000 Gr','50000 Gr'],
    thermometer: ['-18 °C','0 °C','4 °C','10 °C','37 °C','60 °C','75 °C','82 °C','100 °C'],
};

const addDetailUrlTemplate = "{{ route('report-alat-verifications.add-detail', ['uuid' => 'UUID_PLACEHOLDER']) }}";

let modalRowCount = 0;

function buildAlatOptions() {
    let html = '<option value="">-- Pilih Alat --</option>';
    html += '<optgroup label="Timbangan">';
    scales.forEach(s => html += `<option value="${s.uuid}" data-type="scale">${s.label}</option>`);
    html += '</optgroup><optgroup label="Thermometer">';
    thermometers.forEach(t => html += `<option value="${t.uuid}" data-type="thermometer">${t.label}</option>`);
    html += '</optgroup>';
    return html;
}

function buildTitikUkurOptions(alatType) {
    const options = titikUkurOptions[alatType] || [];
    let html = '<option value="">-- Pilih Titik Ukur --</option>';
    options.forEach(opt => html += `<option value="${opt}">${opt}</option>`);
    return html;
}

function addModalRow() {
    const tbody = document.getElementById('modal-detail-body');
    const index = modalRowCount;
    const globalTime = document.getElementById('modal-global-check-time').value;

    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="text-center row-number">${index + 1}</td>
        <td>
            <select name="items[${index}][alat_uuid]" class="form-select form-control alat-select" required>
                ${buildAlatOptions()}
            </select>
            <input type="hidden" name="items[${index}][alat_type]" class="alat-type-input" value="">
        </td>
        <td>
            <select name="items[${index}][titik_ukur]" class="form-select form-control titik-ukur-select" required disabled>
                <option value="">-- Pilih Titik Ukur --</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="items[${index}][nilai_baca]" class="form-control" required placeholder="12.5">
        </td>
        <td>
            <input type="time" name="items[${index}][check_time]" class="form-control check-time-input" value="${globalTime}">
        </td>
        <td>
            <input type="text" name="items[${index}][notes]" class="form-control" placeholder="catatan (opsional)">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button>
        </td>
    `;

    tbody.appendChild(row);
    modalRowCount++;
    renumberModalRows();
}

function renumberModalRows() {
    document.querySelectorAll('#modal-detail-body tr').forEach((row, i) => {
        row.querySelector('.row-number').textContent = i + 1;
    });
}

document.getElementById('modal-detail-body').addEventListener('change', function (e) {
    if (e.target.classList.contains('alat-select')) {
        const row = e.target.closest('tr');
        const alatType = e.target.selectedOptions[0]?.dataset.type ?? '';

        row.querySelector('.alat-type-input').value = alatType;

        const titikUkurSelect = row.querySelector('.titik-ukur-select');
        titikUkurSelect.innerHTML = buildTitikUkurOptions(alatType);
        titikUkurSelect.disabled = !alatType;
    }
});

document.getElementById('modal-detail-body').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        renumberModalRows();
    }
});

document.getElementById('modal-add-row').addEventListener('click', () => addModalRow());

document.getElementById('modal-global-check-time').addEventListener('change', function () {
    document.querySelectorAll('#modal-detail-body .check-time-input').forEach(input => {
        if (!input.dataset.touched) input.value = this.value;
    });
});
document.getElementById('modal-detail-body').addEventListener('input', function (e) {
    if (e.target.classList.contains('check-time-input')) {
        e.target.dataset.touched = 'true';
    }
});

function openAddDetailModal(reportUuid) {
    document.getElementById('modal-detail-body').innerHTML = '';
    modalRowCount = 0;
    document.getElementById('modal-global-check-time').value = '08:00';
    document.getElementById('addDetailForm').action = addDetailUrlTemplate.replace('UUID_PLACEHOLDER', reportUuid);
    addModalRow();
    new bootstrap.Modal(document.getElementById('addDetailModal')).show();
}
</script>
@endsection