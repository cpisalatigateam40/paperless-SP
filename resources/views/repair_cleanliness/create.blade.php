@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Buat Laporan Pemeriksaan</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('repair-cleanliness.store') }}" method="POST">
                @csrf

                <div class="row" style="margin-bottom: 3rem;">
                    <div class="col-md-3">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                    </div>

                    <div class="col-md-3">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" required>
                    </div>
                </div>

                <hr>
                <h5 class="mb-3">Detail Pemeriksaan</h5>
                <div id="detail-wrapper">
                    <div class="detail-item border p-3 mb-3">
                        <div class="mb-2">
                            <label>Mesin / Peralatan</label>
                            <select name="details[0][equipment_uuid]" class="form-control" required>
                                <option value="">Pilih Mesin</option>
                                @foreach ($equipments as $equipment)
                                <option value="{{ $equipment->uuid }}">{{ $equipment->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Section</label>
                            <select name="details[0][section_uuid]" class="form-control" required>
                                <option value="">Pilih Section</option>
                                @foreach ($sections as $section)
                                <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Jenis Perbaikan</label>
                            <input type="text" name="details[0][repair_type]" class="form-control" required>
                        </div>

                        <div class="mb-2">
                            <label>Kondisi Mesin Setelah Perbaikan</label>
                            <select name="details[0][clean_condition]" class="form-control" required>
                                <option value="bersih">Bersih</option>
                                <option value="kotor">Kotor</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Spare Part yang Tertinggal</label>
                            <select name="details[0][spare_part_left]" class="form-control" required>
                                <option value="tidak ada">Tidak Ada</option>
                                <option value="ada">Ada</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Keterangan</label>
                            <input type="text" name="details[0][notes]" class="form-control">
                        </div>
                    </div>
                </div>

                {{-- Tambahkan tombol tambah detail lain jika perlu --}}
                <button type="submit" class="btn btn-success">Simpan Laporan</button>
            </form>
        </div>
    </div>
</div>
@endsection