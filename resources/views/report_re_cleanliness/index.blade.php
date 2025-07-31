@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Daftar Laporan Verifikasi Kebersihan Ruangan, Mesin, dan Peralatan</h4>
            <a href="{{ route('report-re-cleanliness.create') }}" class="btn btn-sm btn-primary">+ Buat Laporan</a>
        </div>
        <div class="card-body">
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

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Area</th>
                        <th>Pemeriksa</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($report->date)->format('d/m/Y') }}</td>
                        <td>{{ optional($report->area)->name }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
                        <td class="d-flex" style="gap: .3rem;">
                            {{-- Toggle Detail --}}
                            <button class="btn btn-sm btn-info" onclick="toggleDetail('{{ $report->uuid }}')"
                                title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Delete --}}
                            <form action="{{ route('report-re-cleanliness.destroy', $report->uuid) }}" method="POST"
                                onsubmit="return confirm('Yakin hapus laporan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Known --}}
                            @can('known report')
                            @if(!$report->known_by)
                            <form action="{{ route('report-re-cleanliness.known', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </form>
                            @else
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                title="Diketahui oleh">
                                <i class="fas fa-check"></i> {{ $report->known_by }}
                            </span>
                            @endif
                            @else
                            @if($report->known_by)
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                title="Diketahui oleh">
                                <i class="fas fa-check"></i> {{ $report->known_by }}
                            </span>
                            @endif
                            @endcan

                            {{-- Approve --}}
                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report-re-cleanliness.approve', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                    <i class="fas fa-thumbs-up"></i>
                                </button>
                            </form>
                            @else
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                title="Disetujui oleh">
                                <i class="fas fa-check"></i> {{ $report->approved_by }}
                            </span>
                            @endif
                            @else
                            @if($report->approved_by)
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;"
                                title="Disetujui oleh">
                                <i class="fas fa-check"></i> {{ $report->approved_by }}
                            </span>
                            @endif
                            @endcan

                            {{-- Export PDF --}}
                            <a href="{{ route('report-re-cleanliness.exportPdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>
                    {{-- Baris detail (ruangan & equipment) --}}
                    <tr id="detail-{{ $report->uuid }}" style="display: none;">
                        <td colspan="5">
                            {{-- Detail Ruangan --}}
                            <h6 style="font-weight: bold;">Pemeriksaan Ruangan</h6>
                            <table class="table table-bordered table-sm mb-4 align-middle">
                                <thead class="align-middle text-center">
                                    <tr>
                                        <th rowspan="2" class="align-middle">No</th>
                                        <th rowspan="2" class="align-middle">Area Produksi / Elemen</th>
                                        <th colspan="2" class="align-middle">Kondisi</th>
                                        <th rowspan="2" class="align-middle">Keterangan</th>
                                        <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                        <th rowspan="2" class="align-middle">Verifikasi Setelah Tindakan Koreksi</th>
                                    </tr>
                                    <tr>
                                        <th>Bersih</th>
                                        <th>Kotor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach ($report->roomDetails->groupBy('room.name') as $roomName => $details)
                                    {{-- Baris judul ruangan --}}
                                    <tr>
                                        <td class="text-center fw-bold">{{ $no++ }}</td>
                                        <td class="fw-bold" colspan="7" style="font-weight: bold;">
                                            {{ strtoupper($roomName) }}
                                        </td>
                                    </tr>

                                    {{-- Baris elemen-elemen ruangan --}}
                                    @foreach ($details as $detail)
                                    <tr>
                                        <td></td> {{-- Kosongkan kolom No --}}
                                        <td>{{ optional($detail->element)->element_name }}</td>
                                        <td class="text-center">
                                            @if ($detail->condition === 'clean') ✔ @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($detail->condition === 'dirty') ✔ @endif
                                        </td>
                                        <td>{{ $detail->notes }}</td>
                                        <td>{{ $detail->corrective_action }}</td>
                                        <td>{{ $detail->verification }}</td>
                                    </tr>

                                    {{-- Koreksi lanjutan --}}
                                    @foreach ($detail->followups as $index => $followup)
                                    <tr class="table-secondary">
                                        <td></td>
                                        <td colspan="3">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                        <td>{{ $followup->notes }}</td>
                                        <td>{{ $followup->corrective_action }}</td>
                                        <td>{{ $followup->verification }}</td>
                                    </tr>
                                    @endforeach
                                    @endforeach

                                    @endforeach
                                </tbody>


                            </table>

                            {{-- Detail Equipment --}}
                            <h6 style="font-weight: bold;">Pemeriksaan Mesin & Peralatan</h6>
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="align-middle text-center">
                                    <tr>
                                        <th rowspan="2" class="align-middle">No</th>
                                        <th rowspan="2" class="align-middle">Peralatan / Part</th>
                                        <th colspan="2" class="align-middle">Kondisi</th>
                                        <th rowspan="2" class="align-middle">Keterangan</th>
                                        <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                        <th rowspan="2" class="align-middle">Verifikasi Setelah Tindakan Koreksi</th>
                                    </tr>
                                    <tr>
                                        <th>Bersih</th>
                                        <th>Kotor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach ($report->equipmentDetails->groupBy('equipment.name') as $equipmentName =>
                                    $details)
                                    {{-- Baris judul peralatan --}}
                                    <tr>
                                        <td class="text-center fw-bold">{{ $no++ }}</td>
                                        <td class="fw-bold" colspan="6" styl e="font-weight: bold;">
                                            {{ strtoupper($equipmentName) }}</td>
                                    </tr>

                                    {{-- Baris part-part dari peralatan --}}
                                    @foreach ($details as $detail)
                                    <tr>
                                        <td></td>
                                        <td>{{ optional($detail->part)->part_name }}</td>
                                        <td class="text-center">
                                            @if ($detail->condition === 'clean') ✔ @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($detail->condition === 'dirty') ✔ @endif
                                        </td>
                                        <td>{{ $detail->notes }}</td>
                                        <td>{{ $detail->corrective_action }}</td>
                                        <td>{{ $detail->verification }}</td>
                                    </tr>

                                    {{-- Koreksi lanjutan --}}
                                    @foreach ($detail->followups as $index => $followup)
                                    <tr class="table-secondary">
                                        <td></td>
                                        <td colspan="3">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                        <td>{{ $followup->notes }}</td>
                                        <td>{{ $followup->corrective_action }}</td>
                                        <td>{{ $followup->verification }}</td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">Belum ada laporan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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

function toggleDetail(uuid) {
    const row = document.getElementById('detail-' + uuid);
    if (row.style.display === 'none') {
        row.style.display = '';
    } else {
        row.style.display = 'none';
    }
}
</script>
@endsection