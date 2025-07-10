@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between">
            <h4>Daftar Report Verifikasi Pemebekuan IQF</h4>
            <a href="{{ route('report_iqf_freezings.create') }}" class="btn btn-primary btn-sm">Buat Report Baru</a>
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
                        <th>Shift</th>
                        <th>Area</th>
                        <th>Dibuat oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reports as $report)
                    <tr>
                        <td>{{ $report->date }}</td>
                        <td>{{ $report->shift }}</td>
                        <td>{{ optional($report->area)->name }}</td>
                        <td>{{ $report->created_by }}</td>
                        <td>
                            <button class="btn btn-sm btn-info toggle-detail"
                                data-target="#detail-{{ $report->id }}">Lihat Detail</button>

                            <form action="{{ route('report_iqf_freezings.destroy', $report->id) }}" method="POST"
                                style="display:inline;" onsubmit="return confirm('Hapus report ini?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>

                            @can('approve report')
                            @if(!$report->approved_by)
                            <form action="{{ route('report_iqf_freezings.approve', $report->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            @else
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                Disetujui oleh {{ $report->approved_by }}
                            </span>
                            @endif
                            @else
                            @if($report->approved_by)
                            <span class="badge bg-success"
                                style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                Disetujui oleh {{ $report->approved_by }}
                            </span>
                            @endif
                            @endcan

                            <a href="{{ route('report_iqf_freezings.export-pdf', $report->uuid) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank">🖨 Cetak PDF</a>
                        </td>
                    </tr>

                    <tr id="detail-{{ $report->id }}" class="d-none">
                        <td colspan="6">
                            <table class="table table-sm table-bordered mb-0 text-center">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle">No</th>
                                        <th rowspan="2" class="align-middle">Nama Produk</th>
                                        <th rowspan="2" class="align-middle">Kode Produksi</th>
                                        <th rowspan="2" class="align-middle">Best Before</th>
                                        <th rowspan="2" class="align-middle">Suhu Produk Sebelum IQF (°C)</th>
                                        <th colspan="2" class="align-middle">Pembekuan IQF</th>
                                        <th colspan="2" class="align-middle">Suhu IQF (°C)</th>
                                    </tr>
                                    <tr>
                                        <th class="align-middle">Jam Mulai Pembekuan</th>
                                        <th class="align-middle">Lama Pembekuan (menit)</th>
                                        <th class="align-middle">Room</th>
                                        <th class="align-middle">Suction</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($report->details as $j => $detail)
                                    <tr>
                                        <td>{{ $j + 1 }}</td>
                                        <td>{{ $detail->product->product_name ?? '-' }}</td>
                                        <td>{{ $detail->production_code ?? '-' }}</td>
                                        <td>{{ $detail->best_before ?? '-' }}</td>
                                        <td>{{ $detail->product_temp_before_iqf ?? '-' }}</td>
                                        <td>{{ $detail->freezing_start_time ?? '-' }}</td>
                                        <td>{{ $detail->freezing_duration ?? '-' }}</td>
                                        <td>{{ $detail->room_temperature ?? '-' }}</td>
                                        <td>{{ $detail->suction_temperature ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                    @if($report->details->isEmpty())
                                    <tr>
                                        <td colspan="10">Belum ada detail produk</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end mt-2">
                                <a href="{{ route('report_iqf_freezings.details.create', $report->uuid) }}"
                                    class="btn btn-sm btn-secondary">+ Tambah Detail Produk</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
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