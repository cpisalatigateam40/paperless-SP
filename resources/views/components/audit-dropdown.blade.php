@props(['item', 'routePrefix'])

@php
    $copyRoute = $routePrefix . '.copy-to-audit';
    $editRoute = $routePrefix . '.edit';
    $isAudit = (bool) $item->is_audit;
    $auditVer = $isAudit ? null : $item->auditVersion;
@endphp

@if(Route::has($copyRoute) && !$isAudit)
    <div class="btn-group ml-1">
        <button type="button" class="btn btn-default btn-sm dropdown-toggle shadow-sm" data-bs-toggle="dropdown"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Menu Audit">
            <i class="fas fa-cog text-purple"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right shadow border-0">
            @if($auditVer)
                <h6 class="dropdown-header text-purple">
                    <i class="fas fa-shield-alt mr-1"></i> Data Audit Tersedia
                </h6>
                <a class="dropdown-item" href="{{ route($editRoute, $auditVer->uuid) }}">
                    <i class="fas fa-edit text-purple mr-2"></i> Edit Data Audit
                </a>
            @else
                <a class="dropdown-item" href="{{ route($copyRoute, $item->uuid) }}">
                    <i class="fas fa-edit text-purple mr-2"></i> Salin & Edit Data Audit
                </a>
            @endif
        </div>
    </div>
@endif