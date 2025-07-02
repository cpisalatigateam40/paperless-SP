@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header">
            <h5>Tambah Detail Inspeksi Conveyor</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <p><strong>Tanggal:</strong> {{ $report->date }}</p>
                <p><strong>Shift:</strong> {{ $report->shift }}</p>
                <p><strong>Area:</strong> {{ $report->area->name ?? '-' }}</p>
                <p><strong>Section:</strong> {{ $report->section->section_name ?? '-' }}</p>
            </div>

            <form action="{{ route('report-conveyor-cleanliness.store-detail', $report->uuid) }}" method="POST">
                @csrf

                <div class="mb-3 mt-3">
                    <h5 class="mb-3">Form Detail Pemeriksaan</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Pukul</th>
                                    <th>Area Conveyor Mesin</th>
                                    <th>Bersih</th>
                                    <th>Kotor</th>
                                    <th>Keterangan</th>
                                    <th>Tindakan Koreksi</th>
                                    <th>Verifikasi</th>
                                    <th>QC</th>
                                    <th>KR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mesins as $i => $name)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>
                                        <input type="time" name="machines[{{ $i }}][time]" class="form-control"
                                            value="{{ \Carbon\Carbon::now()->format('H:i') }}">
                                    </td>
                                    <td class="text-start">
                                        <p class="mb-0">{{ $name }}</p>
                                        <input type="hidden" name="machines[{{ $i }}][machine_name]"
                                            value="{{ $name }}">
                                    </td>
                                    <td>
                                        <input type="radio" name="machines[{{ $i }}][status]" value="bersih">
                                    </td>
                                    <td>
                                        <input type="radio" name="machines[{{ $i }}][status]" value="kotor">
                                    </td>
                                    <td>
                                        <input type="text" name="machines[{{ $i }}][notes]" class="form-control">
                                    </td>
                                    <td>
                                        <input type="text" name="machines[{{ $i }}][corrective_action]"
                                            class="form-control">
                                    </td>
                                    <td>
                                        <select name="machines[{{ $i }}][verification]"
                                            class="form-select form-control verification-select">
                                            <option value="">-- Pilih --</option>
                                            <option value="1">OK</option>
                                            <option value="0">Tidak OK</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="machines[{{ $i }}][qc_check]" value="1">
                                    </td>
                                    <td>
                                        <input type="checkbox" name="machines[{{ $i }}][kr_check]" value="1">
                                    </td>
                                </tr>
                                <tr class="followup-row">
                                    <td colspan="10">
                                        <div class="followup-wrapper"></div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success">Simpan Detail</button>
                    <a href="{{ route('report-conveyor-cleanliness.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type=radio][value=bersih], input[type=radio][value=kotor]').forEach(
        radio => {
            radio.addEventListener('change', function() {
                const row = this.closest('tr');
                const notes = row.querySelector('input[name*="[notes]"]');
                const corrective = row.querySelector('input[name*="[corrective_action]"]');
                const verification = row.querySelector('select[name*="[verification]"]');

                if (this.value === 'bersih') {
                    notes.value = '';
                    corrective.value = '';
                    notes.setAttribute('readonly', true);
                    corrective.setAttribute('readonly', true);
                    verification.value = '1';
                    removeAllFollowups(row);
                } else {
                    notes.removeAttribute('readonly');
                    corrective.removeAttribute('readonly');
                    verification.value = '0';
                    createFollowup(row);
                }
            });
        });

    document.querySelectorAll('select[name*="[verification]"]').forEach(select => {
        select.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.value === '0') {
                createFollowup(row);
            } else {
                removeAllFollowups(row);
            }
        });
    });

    function createFollowup(row) {
        const wrapper = row.nextElementSibling.querySelector('.followup-wrapper');
        if (wrapper && wrapper.children.length === 0) {
            const index = wrapper.children.length;
            const baseName = row.querySelector('select[name*="[verification]"]').name.replace('[verification]',
                '');
            const html = `
                <div class="followup-group border rounded p-2 mb-2">
                    <label class="small mb-1 text-start">Koreksi Lanjutan #${index+1}</label>
                    <input type="text" name="${baseName}[followups][${index}][notes]" class="form-control mb-1" placeholder="Catatan">
                    <input type="text" name="${baseName}[followups][${index}][corrective_action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                    <select name="${baseName}[followups][${index}][verification]" class="form-control followup-verification">
                        <option value="">-- Pilih --</option>
                        <option value="0">Tidak OK</option>
                        <option value="1">OK</option>
                    </select>
                </div>`;
            wrapper.insertAdjacentHTML('beforeend', html);

            const newSelect = wrapper.querySelector('.followup-verification');
            newSelect.addEventListener('change', function() {
                if (this.value === '0') addNextFollowup(wrapper, baseName);
                else removeFollowupsAfter(wrapper, newSelect.closest('.followup-group'));
            });
        }
    }

    function addNextFollowup(wrapper, baseName) {
        const index = wrapper.children.length;
        const html = `
            <div class="followup-group border rounded p-2 mb-2">
                <label class="small mb-1">Koreksi Lanjutan #${index+1}</label>
                <input type="text" name="${baseName}[followups][${index}][notes]" class="form-control mb-1" placeholder="Catatan">
                <input type="text" name="${baseName}[followups][${index}][corrective_action]" class="form-control mb-1" placeholder="Tindakan Koreksi">
                <select name="${baseName}[followups][${index}][verification]" class="form-control followup-verification">
                    <option value="">-- Pilih --</option>
                    <option value="0">Tidak OK</option>
                    <option value="1">OK</option>
                </select>
            </div>`;
        wrapper.insertAdjacentHTML('beforeend', html);

        const newSelect = wrapper.lastElementChild.querySelector('.followup-verification');
        newSelect.addEventListener('change', function() {
            if (this.value === '0') addNextFollowup(wrapper, baseName);
            else removeFollowupsAfter(wrapper, newSelect.closest('.followup-group'));
        });
    }

    function removeAllFollowups(row) {
        const wrapper = row.nextElementSibling.querySelector('.followup-wrapper');
        if (wrapper) wrapper.innerHTML = '';
    }

    function removeFollowupsAfter(wrapper, currentGroup) {
        const all = Array.from(wrapper.querySelectorAll('.followup-group'));
        const idx = all.indexOf(currentGroup);
        all.slice(idx + 1).forEach(el => el.remove());
    }
});
</script>
@endsection