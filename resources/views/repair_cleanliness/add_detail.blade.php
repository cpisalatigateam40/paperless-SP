@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Tambah Detail Pemeriksaan</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('repair-cleanliness.store-detail') }}" method="POST">
                @csrf

                <input type="hidden" name="report_uuid" value="{{ $report->uuid }}">

                <div class="mb-3">
                    <label>Mesin / Peralatan</label>
                    <select name="equipment_uuid" class="form-control" required>
                        <option value="">Pilih Mesin</option>
                        @foreach ($equipments as $equipment)
                            <option value="{{ $equipment->uuid }}">{{ $equipment->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Section</label>
                    <select name="section_uuid" class="form-control" required>
                        <option value="">Pilih Section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->uuid }}">{{ $section->section_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>Jenis Perbaikan</label>
                    <input type="text" name="repair_type" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Kondisi Mesin Setelah Perbaikan</label>
                    <input type="text" name="post_repair_condition" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Keadaan Kebersihan</label>
                    <select name="clean_condition" class="form-control" required>
                        <option value="bersih">Bersih</option>
                        <option value="kotor">Kotor</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Spare Part yang Tertinggal</label>
                    <select name="spare_part_left" class="form-control" required>
                        <option value="tidak ada">Tidak Ada</option>
                        <option value="ada">Ada</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Keterangan</label>
                    <input type="text" name="notes" class="form-control">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('repair-cleanliness.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan Detail</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
