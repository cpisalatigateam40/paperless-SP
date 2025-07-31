@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Data Report Residu Klorin</h4>
            <a href="{{ route('report_chlorine_residues.create') }}" class="btn btn-sm btn-primary">+ Tambah Report</a>
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
                        <th>Area</th>
                        <th>Section</th>
                        <th>Bulan</th>
                        <th>Sampling Point</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td>{{ $report->area->name ?? '-' }}</td>
                        <td>{{ $report->section->section_name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($report->month)->format('F Y') }}</td>
                        <td>{{ $report->sampling_point }}</td>
                        <td class="d-flex" style="gap: .2rem;">
                            {{-- Toggle Detail --}}
                            <button class="btn btn-info btn-sm toggle-detail" data-target="#detail-{{ $report->id }}"
                                title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- Update --}}
                            <a href="{{ route('report_chlorine_residues.edit', $report->uuid) }}"
                                class="btn btn-warning btn-sm" title="Update">
                                <i class="fas fa-pen"></i>
                            </a>

                            {{-- Hapus --}}
                            <form action="{{ route('report_chlorine_residues.destroy', $report->uuid) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Hapus?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                            {{-- Approve --}}
                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_chlorine_residues.approve', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                    <i class="fas fa-thumbs-up"></i>
                                </button>
                            </form>
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
                            <a href="{{ route('report_chlorine_residues.export-pdf', $report->uuid) }}" target="_blank"
                                class="btn btn-outline-secondary btn-sm" title="Cetak PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>

                    </tr>

                    {{-- Detail --}}
                    <tr id="detail-{{ $report->id }}" class="d-none">
                        <td colspan="5">
                            <table class="table table-sm table-bordered mt-2">
                                <thead class="text-center">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Standar (PPM)</th>
                                        <th>Hasil Pemeriksaan (PPM)</th>
                                        <th>Keterangan</th>
                                        <th>Tindakan Koreksi</th>
                                        <th>Verifikasi</th>
                                        <th>Diverifikasi Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report->details as $detail)
                                    <tr>
                                        <td class="text-center">{{ $detail->day }}</td>
                                        <td class="text-center">0,1 - 5</td>
                                        <td>{{ $detail->result_ppm }}</td>
                                        <td>{{ $detail->remark }}</td>
                                        <td>{{ $detail->corrective_action }}</td>
                                        <td>{{ $detail->verification }}</td>
                                        <td>
                                            {{ $detail->verified_by }}
                                            @if($detail->verified_at)
                                            <small>({{ \Carbon\Carbon::parse($detail->verified_at)->format('d-m-Y') }})</small>
                                            @endif
                                        </td>
                                    </tr>

                                    @foreach($detail->followups as $index => $followup)
                                    <tr class="table-secondary">
                                        <td></td>
                                        <td colspan="3">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                        <td>{{ $followup->notes }} - {{ $followup->corrective_action }}</td>
                                        <td>{{ $followup->verification }}</td>
                                        <td></td>
                                    </tr>
                                    @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data.</td>
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

    $('.toggle-detail').on('click', function() {
        const target = $(this.dataset.target);
        const isHidden = target.hasClass('d-none');

        $('.toggle-detail').not(this).text('Lihat Detail'); // reset label
        $('tr[id^="detail-"]').addClass('d-none'); // hide all

        if (isHidden) {
            target.removeClass('d-none');
            $(this).text('Sembunyikan Detail');
        } else {
            target.addClass('d-none');
            $(this).text('Lihat Detail');
        }
    });
});
</script>
@endsection