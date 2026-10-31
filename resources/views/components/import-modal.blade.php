@props([
    'route',
    'templateRoute',
    'title'    => 'Import Data',
    'subtitle' => 'Unggah data dari file Excel',
    'modalId'  => 'modalImport',
    'hint'     => null,
])

@once
<style>
    .imp-modal .modal-content { border: 0; border-radius: 1rem; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,.18); }

    /* Header */
    .imp-modal .imp-header { background: linear-gradient(135deg, #198754, #20c997); color: #fff; padding: 1rem 1.25rem; display: flex; align-items: center; gap: .8rem; }
    .imp-modal .imp-header-icon { width: 2.6rem; height: 2.6rem; border-radius: .75rem; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
    .imp-modal .imp-header h5 { margin: 0; font-weight: 700; font-size: 1.05rem; color: #fff; line-height: 1.25; }
    .imp-modal .imp-header small { color: rgba(255,255,255,.85); }
    .imp-modal .imp-close { margin-left: auto; width: 2rem; height: 2rem; border: 0; border-radius: 50%; background: rgba(255,255,255,.18); color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: .15s; flex-shrink: 0; }
    .imp-modal .imp-close:hover { background: rgba(255,255,255,.3); }

    /* Body: semua section selebar penuh */
    .imp-modal .modal-body { padding: 1.25rem; }
    .imp-modal .imp-section + .imp-section { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #eef0f2; }
    .imp-modal .imp-section-head { display: flex; align-items: center; gap: .55rem; margin-bottom: .7rem; }
    .imp-modal .imp-num { width: 1.5rem; height: 1.5rem; border-radius: 50%; background: #198754; color: #fff; font-weight: 700; font-size: .75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .imp-modal .imp-section-title { font-weight: 700; font-size: .9rem; color: #212529; margin: 0; }

    /* Kartu template */
    .imp-modal .imp-template { display: flex; align-items: center; gap: .8rem; padding: .75rem 1rem; border: 1px solid #dfe6e2; border-radius: .75rem; background: #f8fbf9; text-decoration: none; transition: .15s; }
    .imp-modal .imp-template:hover { border-color: #198754; background: #eef8f2; }
    .imp-modal .imp-template .imp-xls { font-size: 1.7rem; color: #198754; }
    .imp-modal .imp-template strong { display: block; color: #212529; font-size: .9rem; line-height: 1.2; }
    .imp-modal .imp-template small { display: block; color: #6c757d; font-size: .78rem; }
    .imp-modal .imp-template .imp-dl { margin-left: auto; color: #198754; font-size: 1.05rem; }

    /* Form: grid 2 kolom, input seragam */
    .imp-modal .imp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
    .imp-modal .imp-grid .imp-full { grid-column: 1 / -1; }
    .imp-modal .imp-label { display: block; font-size: .78rem; font-weight: 600; color: #495057; margin-bottom: .25rem; }
    .imp-modal .imp-grid .form-control,
    .imp-modal .imp-grid .form-select { width: 100%; height: 2.4rem; font-size: .875rem; border-radius: .5rem; border: 1px solid #ced4da; padding: .35rem .7rem; }
    .imp-modal .imp-grid .form-control:focus,
    .imp-modal .imp-grid .form-select:focus { border-color: #198754; box-shadow: 0 0 0 .2rem rgba(25,135,84,.15); outline: 0; }

    /* Area upload */
    .imp-modal .imp-drop { display: block; margin: 0; border: 2px dashed #c4d4cb; border-radius: .85rem; padding: 1.25rem 1rem; text-align: center; cursor: pointer; background: #fcfdfc; transition: .15s; }
    .imp-modal .imp-drop:hover, .imp-modal .imp-drop.is-over { border-color: #198754; background: #eef8f2; }
    .imp-modal .imp-drop.has-file { border-style: solid; border-color: #198754; background: #eef8f2; }
    .imp-modal .imp-drop.is-error { border-color: #dc3545; background: #fff5f5; }
    .imp-modal .imp-drop-icon { font-size: 1.8rem; color: #198754; margin-bottom: .3rem; line-height: 1; }
    .imp-modal .imp-drop.is-error .imp-drop-icon { color: #dc3545; }
    .imp-modal .imp-file-name { font-weight: 600; font-size: .9rem; color: #212529; word-break: break-all; }
    .imp-modal .imp-file-meta { font-size: .78rem; color: #6c757d; }
    .imp-modal .imp-file-error { font-size: .8rem; color: #dc3545; margin-top: .45rem; display: none; }
    .imp-modal .imp-hint { font-size: .78rem; color: #6c757d; margin-top: .55rem; }

    /* Footer: dua tombol sama lebar */
    .imp-modal .modal-footer { background: #f8f9fa; border-top: 1px solid #eef0f2; padding: .85rem 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
    .imp-modal .modal-footer .btn { margin: 0; height: 2.4rem; font-weight: 600; border-radius: .5rem; }

    .imp-modal form { display: flex; flex-direction: column; max-height: calc(100vh - 2rem); }
    .imp-modal .modal-body { overflow-y: auto; min-height: 0; }
</style>
@endonce

<div class="modal fade imp-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ $route }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="imp-header">
                    <div class="imp-header-icon"><i class="fas fa-file-import"></i></div>
                    <div>
                        <h5>{{ $title }}</h5>
                        <small>{{ $subtitle }}</small>
                    </div>
                    <button type="button" class="imp-close" data-bs-dismiss="modal" aria-label="Tutup">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    {{-- 1. Template --}}
                    <div class="imp-section">
                        <div class="imp-section-head">
                            <span class="imp-num">1</span>
                            <h6 class="imp-section-title">Download template</h6>
                        </div>
                        <a href="{{ $templateRoute }}" class="imp-template">
                            <i class="fas fa-file-excel imp-xls"></i>
                            <span>
                                <strong>Template Excel</strong>
                                <small>Isi data mulai dari baris ke-2</small>
                            </span>
                            <i class="fas fa-download imp-dl"></i>
                        </a>
                    </div>

                    {{-- 2. Isian tambahan (opsional) --}}
                    @if($slot->isNotEmpty())
                    <div class="imp-section">
                        <div class="imp-section-head">
                            <span class="imp-num">2</span>
                            <h6 class="imp-section-title">Data laporan</h6>
                        </div>
                        <div class="imp-grid">
                            {{ $slot }}
                        </div>
                    </div>
                    @endif

                    {{-- 3. Upload --}}
                    <div class="imp-section">
                        <div class="imp-section-head">
                            <span class="imp-num">{{ $slot->isNotEmpty() ? 3 : 2 }}</span>
                            <h6 class="imp-section-title">Upload file</h6>
                        </div>
                        <label class="imp-drop">
                            <input type="file" name="file" class="imp-file d-none" accept=".xlsx,.xls" required>
                            <div class="imp-drop-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div class="imp-file-name">Klik atau seret file ke sini</div>
                            <div class="imp-file-meta">Format .xlsx / .xls, maksimal 10 MB</div>
                        </label>
                        <div class="imp-file-error"></div>
                        @if($hint)
                            <div class="imp-hint"><i class="fas fa-info-circle"></i> {{ $hint }}</div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success imp-submit">
                        <i class="fas fa-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const root   = document.getElementById('{{ $modalId }}');
    const form   = root.querySelector('form');
    const input  = root.querySelector('.imp-file');
    const zone   = root.querySelector('.imp-drop');
    const nameEl = root.querySelector('.imp-file-name');
    const metaEl = root.querySelector('.imp-file-meta');
    const iconEl = root.querySelector('.imp-drop-icon i');
    const errEl  = root.querySelector('.imp-file-error');
    const btn    = root.querySelector('.imp-submit');
    const MAX    = 10 * 1024 * 1024;
    const DEFAULT_META = metaEl.textContent;
    const DEFAULT_NAME = nameEl.textContent;

    function reset() {
        form.reset();
        zone.classList.remove('has-file', 'is-error', 'is-over');
        iconEl.className = 'fas fa-cloud-upload-alt';
        nameEl.textContent = DEFAULT_NAME;
        metaEl.textContent = DEFAULT_META;
        errEl.style.display = 'none';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload me-1"></i> Import';
    }

    function fail(msg) {
        input.value = '';
        zone.classList.remove('has-file');
        zone.classList.add('is-error');
        iconEl.className = 'fas fa-exclamation-circle';
        errEl.textContent = msg;
        errEl.style.display = 'block';
    }

    function show(file) {
        errEl.style.display = 'none';
        zone.classList.remove('is-error');
        if (!/\.(xlsx|xls)$/i.test(file.name)) return fail('Hanya file .xlsx atau .xls yang diterima.');
        if (file.size > MAX)                    return fail('Ukuran file melebihi 10 MB.');
        zone.classList.add('has-file');
        iconEl.className = 'fas fa-check-circle';
        nameEl.textContent = file.name;
        metaEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
    }

    input.addEventListener('change', () => input.files[0] ? show(input.files[0]) : reset());

    ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, e => {
        e.preventDefault(); zone.classList.add('is-over');
    }));
    ['dragleave', 'drop'].forEach(ev => zone.addEventListener(ev, e => {
        e.preventDefault(); zone.classList.remove('is-over');
    }));
    zone.addEventListener('drop', e => {
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            show(input.files[0]);
        }
    });

    form.addEventListener('submit', () => {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
    });

    root.addEventListener('hidden.bs.modal', reset);
})();
</script>