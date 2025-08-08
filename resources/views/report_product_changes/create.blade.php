@extends('layouts.app')

@section('content')
@php
$conditionOptions_1_8 = [
1 => '1 - Bersih, tidak ada sisa bahan/kemasan sebelumnya',
2 => '2 - Ada sisa bahan/kemasan sebelumnya',
3 => '3 - Bebas dari kontaminan dan bahan sebelumnya',
4 => '4 - Ada kontaminan atau sisa bahan sebelumnya',
5 => '5 - Bebas dari potensi kontaminasi allergen',
6 => '6 - Ada potensi kontaminasi allergen',
7 => '7 - Bersih, tidak ada kontaminan/kotoran, tidak tercium bau menyimpang',
8 => '8 - Tidak bersih, ada kontaminan/kotoran, tercium bau menyimpang',
];

$conditionOptions_3_8 = array_slice($conditionOptions_1_8, 2, 6, true); // dari key 3 sampai 8
@endphp

<div class="container-fluid">
    <form action="{{ route('report_product_changes.store') }}" method="POST">
        @csrf

        {{-- HEADER --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Form Verifikasi Pergantian Produk</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-3 mb-2">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control"
                        value="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Shift</label>
                    <input type="text" name="shift" class="form-control" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Produk</label>
                    <select name="product_uuid" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                        <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label>Kode Produksi</label>
                    <input type="text" name="production_code" class="form-control" required>
                </div>
            </div>
        </div>

        {{-- SISA BAHAN DAN KEMASAN --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Sisa Bahan dan Kemasan</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">Item</th>
                            <th class="text-center">Kondisi</th>
                            <th class="text-center">Tindakan Koreksi</th>
                            <th class="text-center">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materialItems as $i => $item)
                        <tr>
                            <td class="align-middle">
                                <input type="hidden" name="material_leftovers[{{ $i }}][item]" value="{{ $item }}">
                                {{ $item }}
                            </td>
                            <td>
                                <select name="material_leftovers[{{ $i }}][condition]"
                                    class="form-control condition-select" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_1_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="material_leftovers[{{ $i }}][corrective_action]"
                                    class="form-control corrective-action" readonly>
                            </td>
                            <td>
                                <select name="material_leftovers[{{ $i }}][verification]"
                                    class="form-control verification-select">
                                    <option value="">--Pilih--</option>
                                    <option value="0">Tidak OK</option>
                                    <option value="1">OK</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="followup-row">
                            <td colspan="4">
                                <div class="followup-wrapper"></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MESIN & PERALATAN --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Mesin dan Peralatan</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle text-center">Nama Peralatan</th>
                            <th class="align-middle text-center">Kondisi</th>
                            <th class="align-middle text-center">Tindakan Koreksi</th>
                            <th class="align-middle text-center">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($equipments as $i => $equipment)
                        <tr>
                            <td class="align-middle">
                                <input type="hidden" name="equipments[{{ $i }}][equipment_uuid]"
                                    value="{{ $equipment->uuid }}">
                                {{ $equipment->name }}
                            </td>
                            <td>
                                <select name="equipments[{{ $i }}][condition]" class="form-control condition-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_3_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="equipments[{{ $i }}][corrective_action]"
                                    class="form-control corrective-action" readonly>
                            </td>
                            <td>
                                <select name="equipments[{{ $i }}][verification]"
                                    class="form-control verification-select">
                                    <option value="">--Pilih--</option>
                                    <option value="0">Tidak OK</option>
                                    <option value="1">OK</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="followup-row">
                            <td colspan="4">
                                <div class="followup-wrapper"></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- KONDISI RUANGAN --}}
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5>Kondisi Ruangan</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle text-center">Nama Ruangan</th>
                            <th class="align-middle text-center">Kondisi</th>
                            <th class="align-middle text-center">Tindakan Koreksi</th>
                            <th class="align-middle text-center">Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sections as $i => $section)
                        <tr>
                            <td class="align-middle">
                                <input type="hidden" name="sections[{{ $i }}][section_uuid]"
                                    value="{{ $section->uuid }}">
                                {{ $section->section_name }}
                            </td>
                            <td>
                                <select name="sections[{{ $i }}][condition]" class="form-control condition-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($conditionOptions_3_8 as $val => $desc)
                                    <option value="{{ $val }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="sections[{{ $i }}][corrective_action]"
                                    class="form-control corrective-action" readonly>
                            </td>
                            <td>
                                <select name="sections[{{ $i }}][verification]"
                                    class="form-control verification-select">
                                    <option value="">--Pilih--</option>
                                    <option value="0">Tidak OK</option>
                                    <option value="1">OK</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="followup-row">
                            <td colspan="4">
                                <div class="followup-wrapper"></div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="text-end">
            <button class="btn btn-success">Simpan</button>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.condition-select').forEach(select => {
        select.addEventListener('change', function() {
            const row = select.closest('tr');
            const corrective = row.querySelector('.corrective-action');
            const verification = row.querySelector('.verification-select');
            const val = parseInt(select.value);

            clearFollowups(row);

            if ([1, 3, 5, 7].includes(val)) {
                corrective.value = '';
                corrective.setAttribute('readonly', true);
                verification.value = '1';
            } else if ([2, 4, 6, 8].includes(val)) {
                corrective.removeAttribute('readonly');
                verification.value = '0';
                addFollowupField(row);
            } else {
                corrective.value = '';
                corrective.setAttribute('readonly', true);
                verification.value = '0';
            }
        });
    });

    document.querySelectorAll('.verification-select').forEach(select => {
        select.addEventListener('change', function() {
            const row = select.closest('tr');
            clearFollowups(row);

            if (this.value === '0') {
                addFollowupField(row);
            }
        });
    });

    function addFollowupField(row) {
        const wrapper = getFollowupWrapper(row);
        const baseName = row.querySelector('.verification-select').name.replace('[verification]', '');
        const count = wrapper.querySelectorAll('.followup-group').length;

        const html = `
            <div class="followup-group border rounded p-2 mb-2">
                <label class="small mb-1">Koreksi Lanjutan #${count+1}</label>
                <input type="text" name="${baseName}[followups][${count}][notes]" class="form-control mb-1" placeholder="Catatan">
                <input type="text" name="${baseName}[followups][${count}][action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                <select name="${baseName}[followups][${count}][verification]" class="form-control followup-verification">
                    <option value="">--Pilih--</option>
                    <option value="0">Tidak OK</option>
                    <option value="1">OK</option>
                </select>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);

        const newSelect = wrapper.querySelectorAll('.followup-verification')[count];
        newSelect.addEventListener('change', function() {
            const followups = wrapper.querySelectorAll('.followup-group');
            const idx = Array.from(followups).indexOf(this.closest('.followup-group'));

            if (this.value === '0') {
                if (idx === followups.length - 1) {
                    addFollowupField(row);
                }
            } else {
                for (let i = followups.length - 1; i > idx; i--) {
                    followups[i].remove();
                }
            }
        });
    }

    function clearFollowups(row) {
        getFollowupWrapper(row).innerHTML = '';
    }

    function getFollowupWrapper(row) {
        return row.nextElementSibling.querySelector('.followup-wrapper');
    }
});
</script>
@endsection