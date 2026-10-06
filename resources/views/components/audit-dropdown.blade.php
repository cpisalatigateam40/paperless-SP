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
    <div class="btn-group ml-1">
        <button type="button" class="btn btn-default btn-sm dropdown-toggle shadow-sm" data-bs-toggle="dropdown"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Menu Audit">
            <i class="fas fa-cog text-purple"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right shadow border-0">
            @if($isAudit)
                <h6 class="dropdown-header text-purple">
                    <i class="fas fa-shield-alt mr-1"></i> Data Audit
                </h6>
            @elseif($auditVer)
                <h6 class="dropdown-header text-purple">
                    <i class="fas fa-shield-alt mr-1"></i> Data Audit Tersedia
                </h6>
                <a class="dropdown-item" href="{{ route($editRoute, $auditVer->uuid) }}">
                    <i class="fas fa-edit text-purple mr-2"></i> Edit Manual
                </a>
            @else
                <a class="dropdown-item" href="{{ route($copyRoute, $item->uuid) }}">
                    <i class="fas fa-edit text-purple mr-2"></i> Salin & Edit Manual
                </a>
            @endif

            @if($canAuto)
                @if(!$isAudit)
                    <div class="dropdown-divider"></div>
                @endif

                <form action="{{ route($autoRoute, $item->uuid) }}" method="POST"
                    onsubmit="return confirm('Ubah semua ketidaksesuaian di data audit menjadi OK secara otomatis?')">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-magic text-purple mr-2"></i> Edit Otomatis (jadikan OK)
                    </button>
                </form>
            @endif
        </div>
    </div>
@endif
@endhasanyrole