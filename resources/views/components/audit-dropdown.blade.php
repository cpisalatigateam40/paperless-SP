@hasanyrole('admin|superadmin|SPV QC')
@props(['item', 'routePrefix'])

@php
    $copyRoute = $routePrefix . '.copy-to-audit';
    $editRoute = $routePrefix . '.edit';
    $autoRoute = $routePrefix . '.auto-audit';
    $isAudit = (bool) $item->is_audit;
    $auditVer = $isAudit ? null : $item->auditVersion;
    $canAuto = Route::has($autoRoute)
        && method_exists($item, 'hasAuditNormalizeRules')
        && $item->hasAuditNormalizeRules();
@endphp

@if(Route::has($copyRoute) && (!$isAudit || $canAuto))

    @once
        @push('styles')
        <style>
            /* Tombol gear: outline ungu */
            .audit-dd__toggle {
                color: #6f42c1;
                background: #fff;
                border: 1px solid #6f42c1;
                box-shadow: none;
                transition: all .15s;
            }
            .audit-dd__toggle:hover,
            .audit-dd__toggle:focus,
            .show > .audit-dd__toggle {
                color: #fff;
                background: #6f42c1;
                border-color: #6f42c1;
                box-shadow: 0 2px 8px rgba(111, 66, 193, .35);
            }
            .audit-dd__toggle:focus {
                box-shadow: 0 0 0 .2rem rgba(111, 66, 193, .25);
            }

            /* Menu dropdown */
            .audit-dd__menu {
                min-width: 15rem;
                padding: 0 0 .4rem;
                overflow: hidden;
                border: 1px solid #e4d6f7 !important;
                border-radius: 10px;
                box-shadow: 0 8px 24px rgba(76, 40, 130, .25) !important;
            }
            .audit-dd__header {
                display: flex;
                align-items: center;
                gap: 6px;
                margin: 0 0 .35rem;
                padding: .6rem 1rem;
                font-size: .78rem;
                font-weight: 700;
                letter-spacing: .3px;
                color: #fff;
                background: linear-gradient(135deg, #6f42c1 0%, #4c2882 100%);
            }
            .audit-dd__item {
                display: flex;
                align-items: center;
                width: 100%;
                padding: .5rem 1rem;
                font-size: .85rem;
                font-weight: 600;
                color: #4c2882;
                background: transparent;
                border: 0;
                text-align: left;
                cursor: pointer;
            }
            .audit-dd__item i {
                width: 1.4rem;
                color: #6f42c1;
            }
            .audit-dd__item:hover,
            .audit-dd__item:focus {
                color: #fff;
                background: #6f42c1;
                text-decoration: none;
                outline: none;
            }
            .audit-dd__item:hover i,
            .audit-dd__item:focus i {
                color: #fff;
            }
            .audit-dd__menu .dropdown-divider {
                margin: .35rem 0;
                border-top-color: #eee5f8;
            }
        </style>
        @endpush
    @endonce

    <div class="btn-group ml-1">
        <button type="button" class="btn btn-sm dropdown-toggle audit-dd__toggle"
            data-bs-toggle="dropdown" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false" title="Menu Audit">
            <i class="fas fa-cog"></i>
        </button>

        <div class="dropdown-menu dropdown-menu-right audit-dd__menu">
            <div class="audit-dd__header">
                <i class="fas fa-shield-alt"></i>
                @if($isAudit)
                    Data Audit
                @elseif($auditVer)
                    Data Audit Tersedia
                @else
                    Menu Audit
                @endif
            </div>

            @if($isAudit)
                {{-- data audit: hanya menu otomatis di bawah --}}
            @elseif($auditVer)
                <a class="audit-dd__item" href="{{ route($editRoute, $auditVer->uuid) }}">
                    <i class="fas fa-edit"></i> Edit Manual
                </a>
            @else
                <a class="audit-dd__item" href="{{ route($copyRoute, $item->uuid) }}">
                    <i class="fas fa-edit"></i> Salin &amp; Edit Manual
                </a>
            @endif

            @if($canAuto)
                @if(!$isAudit)
                    <div class="dropdown-divider"></div>
                @endif

                <form action="{{ route($autoRoute, $item->uuid) }}" method="POST"
                    onsubmit="return confirm('Ubah semua ketidaksesuaian di data audit menjadi OK secara otomatis?')">
                    @csrf
                    <button type="submit" class="audit-dd__item">
                        <i class="fas fa-magic"></i> Edit Otomatis (jadikan OK)
                    </button>
                </form>
            @endif
        </div>
    </div>
@endif
@endhasanyrole