@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="mb-3">Form Pemeriksaan Barang Mudah Pecah</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('report-fragile-item.store') }}" method="POST">
                @csrf
                <div class="row" style="margin-bottom: 2rem;">
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
                    </div>
                </div>

                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Pemilik</th>
                            <th>Jumlah</th>
                            <th>Waktu Awal</th>
                            <th>Waktu Akhir</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($fragileItems->groupBy('section_name') as $section => $items)
                            <tr>
                                <td colspan="7"><strong>{{ $section }}</strong></td>
                            </tr>
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>
                                        {{ $item->item_name }}
                                        <input type="hidden" name="items[{{ $item->uuid }}][fragile_item_uuid]" value="{{ $item->uuid }}">
                                    </td>
                                    <td>{{ $item->owner }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>
                                        <input type="time" name="items[{{ $item->uuid }}][time_start]" class="form-control">
                                    </td>
                                    <td>
                                        <input type="time" name="items[{{ $item->uuid }}][time_end]" class="form-control">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $item->uuid }}][notes]" class="form-control">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>

                <button class="btn btn-primary" style="margin-top: 1rem;">Simpan Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection
