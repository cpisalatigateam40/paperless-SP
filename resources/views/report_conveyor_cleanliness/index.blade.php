@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h5>Daftar Laporan Pemeriksaan Kebersihan Conveyor</h5>
            <a href="{{ route('report-conveyor-cleanliness.create') }}" class="btn btn-sm btn-primary">
                + Tambah Laporan
            </a>
        </div>

        <div class="card-body table-responsive">
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
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Section</th>
                        <th>Dibuat Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $i => $report)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $report->date ?? '-' }}</td>
                            <td>{{ $report->shift ?? '-' }}</td>
                            <td>{{ $report->area->name ?? '-' }}</td>
                            <td>{{ $report->section->section_name ?? '-' }}</td>
                            <td>{{ $report->created_by ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $report->id }}">Lihat Detail</button>

                                <form action="{{ route('report-conveyor-cleanliness.destroy', $report->uuid) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="7">
                                <table class="table table-sm table-bordered mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">No</th>
                                            <th rowspan="2">Pukul</th>
                                            <th rowspan="2">Area Conveyor Mesin</th>
                                            <th colspan="2">Kondisi</th>
                                            <th rowspan="2">Keterangan</th>
                                            <th rowspan="2">Tindakan Koreksi</th>
                                            <th rowspan="2">Verifikasi Setelah Dilakukan Tindakan Koreksi</th>
                                            <th colspan="2">Dicek Oleh</th>
                                        </tr>
                                        <tr>
                                            <th>Bersih</th>
                                            <th>Kotor</th>
                                            <th>QC</th>
                                            <th>KR</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $grouped = $report->machines->chunk(4); @endphp
                                        @foreach ($grouped as $groupIndex => $group)
                                            @foreach ($group as $i => $machine)
                                                <tr>
                                                    <td>{{ $i === 0 ? $groupIndex + 1 : '' }}</td>
                                                    <td>
                                                        @if ($i === 0)
                                                            {{ $group[0]->time ? \Illuminate\Support\Carbon::parse($group[0]->time)->format('H:i') : '-' }}
                                                        @endif
                                                    </td>
                                                    <td class="text-start">{{ $machine->machine_name }}</td>
                                                    <td>{!! $machine->status === 'bersih' ? '✓' : '' !!}</td>
                                                    <td>{!! $machine->status === 'kotor' ? 'X' : '' !!}</td>
                                                    <td>{{ $machine->notes ?? '-' }}</td>
                                                    <td>{{ $machine->corrective_action ?? '-' }}</td>
                                                    <td>{{ $machine->verification ?? '-' }}</td>
                                                    <td>{!! $machine->qc_check ? '✓' : '' !!}</td>
                                                    <td>{!! $machine->kr_check ? '✓' : '' !!}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="d-flex justify-content-end mt-3">
                                    <a href="{{ route('report-conveyor-cleanliness.add-detail', $report->uuid) }}" class="btn btn-sm btn-primary">
                                        + Tambah Detail Inspeksi
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Belum ada laporan</td>
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
    $(document).ready(function () {
        setTimeout(() => {
            $('#success-alert').fadeOut('slow');
            $('#error-alert').fadeOut('slow');
        }, 3000);
    });

    $(document).ready(function () {
        $('.toggle-detail').on('click', function () {
            const target = $(this.dataset.target);
            const isHidden = target.hasClass('d-none');

            // Tutup semua detail lain
            $('.toggle-detail').not(this).text('Lihat Detail');
            $('tr[id^="detail-"]').addClass('d-none');

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
