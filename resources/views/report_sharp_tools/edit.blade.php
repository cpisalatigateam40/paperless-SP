@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <form action="{{ route('report_sharp_tools.update', $report->uuid) }}" method="POST">
        @csrf @method('PUT')
        <div class="card shadow">
            <div class="card-header">
                <h4>Edit Laporan Benda Tajam</h4>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th class="align-middle text-center">#</th>
                            <th class="align-middle">Nama Alat</th>
                            <th class="align-middle text-center">Jumlah Awal</th>
                            <th class="align-middle text-center">Jumlah Akhir</th>
                            <th class="align-middle text-center">Waktu 1</th>
                            <th class="align-middle text-center">Kondisi 1</th>
                            <th class="align-middle text-center">Waktu 2</th>
                            <th class="align-middle text-center">Kondisi 2</th>
                            <th class="align-middle text-center">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report->details as $i => $detail)
                        <tr>
                            <td class="align-middle text-center">{{ $i + 1 }}</td>
                            <td class="align-middle">{{ $detail->sharpTool->name ?? '-' }}</td>
                            <td class="align-middle text-center">
                                <input type="number" class="form-control form-control-sm text-center"
                                    value="{{ $detail->qty_start }}" readonly>
                            </td>
                            <td class="align-middle text-center">
                                <input type="number" name="details[{{ $detail->id }}][qty_end]"
                                    class="form-control form-control-sm text-center" value="{{ $detail->qty_end }}">
                            </td>
                            <td class="align-middle text-center">
                                <input type="time" class="form-control form-control-sm"
                                    value="{{ $detail->check_time_1 }}" disabled>
                            </td>
                            <td class="align-middle text-center">
                                <input type="text" class="form-control form-control-sm"
                                    value="{{ $detail->condition_1 }}" disabled>
                            </td>
                            <td class="align-middle text-center">
                                <input type="time" name="details[{{ $detail->id }}][check_time_2]"
                                    value="{{ $detail->check_time_2 }}" class="form-control form-control-sm">
                            </td>
                            <td class="align-middle text-center">
                                <select name="details[{{ $detail->id }}][condition_2]"
                                    class="form-control form-control-sm">
                                    <option value="">-</option>
                                    <option value="baik" {{ $detail->condition_2 == 'baik' ? 'selected' : '' }}>Baik
                                    </option>
                                    <option value="rusak" {{ $detail->condition_2 == 'rusak' ? 'selected' : '' }}>Rusak
                                    </option>
                                    <option value="hilang" {{ $detail->condition_2 == 'hilang' ? 'selected' : '' }}>
                                        Hilang</option>
                                    <option value="tidaktersedia"
                                        {{ $detail->condition_2 == 'tidaktersedia' ? 'selected' : '' }}>Tidak Tersedia
                                    </option>
                                </select>
                            </td>
                            <td class="align-middle text-center">
                                <input type="text" name="details[{{ $detail->id }}][note]"
                                    class="form-control form-control-sm" value="{{ $detail->note }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route('report_sharp_tools.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection