@php
    $copyRoute = $routePrefix . '.copy-to-audit';
    $showRoute = $routePrefix . '.show';
    $editRoute = $routePrefix . '.edit';
    $hasAuditRoutes = $hasRoute($copyRoute) && $hasRoute($showRoute);
    $itemUuid = $getItemUuid();
    $auditVersionUuid = null;
    if ($hasAuditVersion) {
        $auditVersionUuid = $itemUuid;
    } elseif (is_object($item) && method_exists($item, 'auditVersion')) {
        $auditVer = $item->auditVersion;
        $auditVersionUuid = $auditVer ? $auditVer->uuid : null;
    }
@endphp
@if($hasAuditRoutes && !$isAuditItem())
    <div class="btn-group ml-1">
    <button type="button"
    class="btn btn-default btn-sm dropdown-toggle shadow-sm"
    data-toggle="dropdown"
    aria-haspopup="true"
    aria-expanded="false"
    title="Menu Audit">
    <i class="fas fa-cog text-purple"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right shadow border-0">
    @if($auditVersionUuid)
        {{-- Sudah ada salinan audit --}}
        <h6 class="dropdown-header text-purple">
        <i class="fas fa-shield-alt mr-1"></i> Data Audit Tersedia
        </h6>
        @if($canEditAudit() && $hasRoute($editRoute))
            <a class="dropdown-item" href="{{ route($editRoute, $auditVersionUuid) }}">
            <i class="fas fa-edit text-purple mr-2"></i> Edit Data Audit
            </a>
        @endif
    @else
        {{-- Belum ada salinan audit (Auto-copy & Buka Form Edit Audit) --}}
        @if($canEditAudit() || $canCopyAudit())
            <a class="dropdown-item" href="{{ route($copyRoute, $itemUuid) }}">
            <i class="fas fa-edit text-purple mr-2"></i> Edit Data Audit
            </a>
        @else
            <span class="dropdown-item text-muted disabled">
            <i class="fas fa-lock mr-2"></i> Belum ada data audit
            </span>
        @endif
    @endif
    </div>
    </div>
@endif