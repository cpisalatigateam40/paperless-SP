@hasanyrole('admin|superadmin|SPV QC')
@props(['routePrefix', 'maxDays' => 92])

@php($bulkRoute = $routePrefix . '.bulk-auto-audit')

@if (Route::has($bulkRoute))

    @once
        @push('styles')
        <style>
            .btn-audit-bulk {
                color: #fff;
                background: linear-gradient(135deg, #6f42c1 0%, #4c2882 100%);
                border: 0;
                box-shadow: 0 2px 8px rgba(111, 66, 193, .35);
            }
            .btn-audit-bulk:hover,
            .btn-audit-bulk:focus {
                color: #fff;
                filter: brightness(1.12);
            }
            .btn-audit-bulk:disabled {
                opacity: .55;
                cursor: not-allowed;
            }

            .audit-bulk .modal-content {
                border: 0;
                border-radius: 14px;
                overflow: hidden;
                box-shadow: 0 10px 40px rgba(76, 40, 130, .35);
            }
            .audit-bulk__header {
                position: relative;
                display: flex;
                align-items: center;
                padding: 16px 20px;
                color: #fff;
                background: linear-gradient(135deg, #6f42c1 0%, #4c2882 100%);
                overflow: hidden;
            }
            .audit-bulk__header::after {
                content: "";
                position: absolute;
                right: -30px;
                top: -40px;
                width: 120px;
                height: 120px;
                border-radius: 50%;
                background: rgba(255, 255, 255, .08);
                pointer-events: none;
            }
            .audit-bulk__icon {
                flex-shrink: 0;
                width: 42px;
                height: 42px;
                margin-right: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #fff;
                color: #6f42c1;
                font-size: 1.1rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
            }
            .audit-bulk__title {
                margin: 0;
                font-size: 1.05rem;
                font-weight: 700;
            }
            .audit-bulk__subtitle {
                margin: 2px 0 0;
                font-size: .8rem;
                color: #e9d8fd;
            }
            .audit-bulk__close {
                position: relative;
                z-index: 1;
                margin-left: auto;
                color: #fff;
                opacity: .85;
                text-shadow: none;
                background: transparent;
                border: 0;
                font-size: 1.6rem;
                line-height: 1;
            }
            .audit-bulk__close:hover { color: #fff; opacity: 1; }

            .audit-bulk__note {
                display: flex;
                gap: 10px;
                padding: 10px 12px;
                margin-bottom: 14px;
                font-size: .82rem;
                line-height: 1.45;
                color: #4c2882;
                background: #f3ecfc;
                border-left: 4px solid #6f42c1;
                border-radius: 8px;
            }
            .audit-bulk__note i { margin-top: 2px; color: #6f42c1; }

            .audit-bulk__limit {
                display: flex;
                gap: 10px;
                padding: 10px 12px;
                margin-bottom: 16px;
                font-size: .82rem;
                line-height: 1.45;
                color: #7a4b00;
                background: #fff6e5;
                border-left: 4px solid #f5a623;
                border-radius: 8px;
            }
            .audit-bulk__limit i { margin-top: 2px; color: #f5a623; }

            .audit-bulk label.audit-bulk__label {
                margin-bottom: 4px;
                font-size: .78rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: .4px;
                color: #6c757d;
            }
            .audit-bulk .form-control:focus {
                border-color: #6f42c1;
                box-shadow: 0 0 0 .2rem rgba(111, 66, 193, .2);
            }

            .audit-bulk__presets {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin: 2px 0 14px;
            }
            .audit-bulk__preset {
                padding: 4px 11px;
                font-size: .76rem;
                font-weight: 600;
                color: #6f42c1;
                background: #fff;
                border: 1px solid #d9c7f2;
                border-radius: 999px;
                cursor: pointer;
                transition: all .15s;
            }
            .audit-bulk__preset:hover {
                color: #fff;
                background: #6f42c1;
                border-color: #6f42c1;
            }

            .audit-bulk__counter {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 9px 12px;
                font-size: .85rem;
                font-weight: 600;
                border-radius: 8px;
                transition: all .15s;
            }
            .audit-bulk__counter.is-ok  { color: #1e7e34; background: #e6f6ea; }
            .audit-bulk__counter.is-bad { color: #b02a37; background: #fdecee; }
            .audit-bulk__counter.is-idle { color: #6c757d; background: #f1f3f5; }

            .audit-bulk__footer {
                background: #faf8fd;
                border-top: 1px solid #eee5f8;
            }
        </style>
        @endpush
    @endonce

    <button type="button" class="btn btn-sm btn-audit-bulk"
        data-toggle="modal" data-target="#auditBulkAutoModal"
        data-bs-toggle="modal" data-bs-target="#auditBulkAutoModal">
        <i class="fas fa-magic mr-1"></i> Edit Otomatis Massal
    </button>

    <div class="modal fade audit-bulk" id="auditBulkAutoModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form action="{{ route($bulkRoute) }}" method="POST" class="modal-content" id="auditBulkForm"
                onsubmit="return confirm('Semua ketidaksesuaian pada rentang tanggal ini akan diubah menjadi OK di data audit. Lanjutkan?')">
                @csrf

                <div class="audit-bulk__header">
                    <div class="audit-bulk__icon"><i class="fas fa-user-shield"></i></div>
                    <div>
                        <h5 class="audit-bulk__title">Edit Otomatis Massal</h5>
                        <p class="audit-bulk__subtitle">Koreksi data audit berdasarkan rentang tanggal</p>
                    </div>
                    <button type="button" class="audit-bulk__close" data-dismiss="modal" data-bs-dismiss="modal"
                        aria-label="Tutup">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="audit-bulk__note">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            Perubahan hanya berlaku pada <strong>salinan data audit</strong>. Laporan yang belum punya
                            salinan akan disalin otomatis <strong>hanya jika ada yang perlu diubah</strong>.
                        </div>
                    </div>

                    <div class="audit-bulk__limit">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            Rentang tanggal <strong>maksimal {{ $maxDays }} hari</strong> per proses.
                            Untuk periode yang lebih panjang, jalankan beberapa kali per periode.
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label class="audit-bulk__label" for="auditBulkStart">
                                <i class="far fa-calendar-check mr-1"></i> Dari tanggal
                            </label>
                            <input type="date" id="auditBulkStart" name="start_date" class="form-control" required
                                value="{{ old('start_date', now()->startOfMonth()->toDateString()) }}">
                        </div>
                        <div class="form-group col-6">
                            <label class="audit-bulk__label" for="auditBulkEnd">
                                <i class="far fa-calendar-check mr-1"></i> Sampai tanggal
                            </label>
                            <input type="date" id="auditBulkEnd" name="end_date" class="form-control" required
                                value="{{ old('end_date', now()->toDateString()) }}">
                        </div>
                    </div>

                    <div class="audit-bulk__presets">
                        <button type="button" class="audit-bulk__preset" data-preset="7">7 hari terakhir</button>
                        <button type="button" class="audit-bulk__preset" data-preset="30">30 hari terakhir</button>
                        <button type="button" class="audit-bulk__preset" data-preset="month">Bulan ini</button>
                        <button type="button" class="audit-bulk__preset" data-preset="lastmonth">Bulan lalu</button>
                        <button type="button" class="audit-bulk__preset" data-preset="{{ $maxDays }}">{{ $maxDays }} hari terakhir</button>
                    </div>

                    <div class="audit-bulk__counter is-idle" id="auditBulkCounter">
                        <i class="fas fa-hourglass-half"></i>
                        <span id="auditBulkCounterText">Pilih rentang tanggal</span>
                    </div>

                    @error('end_date')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer audit-bulk__footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-audit-bulk" id="auditBulkSubmit">
                        <i class="fas fa-play mr-1"></i> Jalankan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var MAX = {{ (int) $maxDays }};
            var start = document.getElementById('auditBulkStart');
            var end = document.getElementById('auditBulkEnd');
            var counter = document.getElementById('auditBulkCounter');
            var text = document.getElementById('auditBulkCounterText');
            var submit = document.getElementById('auditBulkSubmit');
            if (!start || !end) return;

            function fmt(d) {
                var m = String(d.getMonth() + 1).padStart(2, '0');
                var day = String(d.getDate()).padStart(2, '0');
                return d.getFullYear() + '-' + m + '-' + day;
            }

            function setState(cls, icon, msg, disabled) {
                counter.className = 'audit-bulk__counter ' + cls;
                counter.querySelector('i').className = 'fas ' + icon;
                text.textContent = msg;
                submit.disabled = disabled;
            }

            function update() {
                if (!start.value || !end.value) {
                    return setState('is-idle', 'fa-hourglass-half', 'Pilih rentang tanggal', true);
                }

                var s = new Date(start.value + 'T00:00:00');
                var e = new Date(end.value + 'T00:00:00');
                var diff = Math.round((e - s) / 86400000);

                if (diff < 0) {
                    return setState('is-bad', 'fa-exclamation-circle',
                        'Tanggal akhir tidak boleh sebelum tanggal awal', true);
                }
                if (diff > MAX) {
                    return setState('is-bad', 'fa-exclamation-triangle',
                        'Rentang ' + diff + ' hari melebihi batas ' + MAX + ' hari. Persempit rentangnya.', true);
                }

                setState('is-ok', 'fa-check-circle',
                    'Rentang ' + diff + ' hari (batas ' + MAX + ' hari)', false);
            }

            document.querySelectorAll('#auditBulkForm [data-preset]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var p = btn.getAttribute('data-preset');
                    var today = new Date();
                    var s, e = today;

                    if (p === 'month') {
                        s = new Date(today.getFullYear(), today.getMonth(), 1);
                    } else if (p === 'lastmonth') {
                        s = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        e = new Date(today.getFullYear(), today.getMonth(), 0);
                    } else {
                        s = new Date(today);
                        s.setDate(s.getDate() - parseInt(p, 10));
                    }

                    start.value = fmt(s);
                    end.value = fmt(e);
                    update();
                });
            });

            start.addEventListener('change', update);
            end.addEventListener('change', update);
            update();
        })();
    </script>
@endif
@endhasanyrole