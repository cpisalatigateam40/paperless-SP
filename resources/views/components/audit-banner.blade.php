@props([
    'title' => 'MODE DATA AUDIT',
    'message' => null,
    'chips' => [
        ['icon' => 'fas fa-shield-alt', 'label' => 'Data operasional aman'],
        ['icon' => 'fas fa-history', 'label' => 'Perubahan tercatat'],
    ],
])

@php(view()->share('auditMode', true))

@once
    @push('styles')
    <style>
        .audit-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 20px;
            margin-bottom: 1rem;
            color: #fff;
            background: linear-gradient(135deg, #6f42c1 0%, #4c2882 100%);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(111, 66, 193, .3);
            position: relative;
            overflow: hidden;
        }
        .audit-banner::after {
            content: "";
            position: absolute;
            right: -30px;
            top: -30px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            pointer-events: none;
        }
        .audit-banner__icon {
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            margin-right: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #fff;
            color: #6f42c1;
            font-size: 1.25rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .15);
        }
        .audit-banner__title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: .3px;
        }
        .audit-banner__text {
            margin: 2px 0 0;
            font-size: .86rem;
            line-height: 1.4;
            color: #e9d8fd;
        }
        .audit-banner__chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            position: relative;
            z-index: 1;
        }
        .audit-banner__chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            font-size: .78rem;
            font-weight: 600;
            color: #fff;
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 999px;
            white-space: nowrap;
        }
        @media (max-width: 575.98px) {
            .audit-banner { padding: 12px 14px; }
            .audit-banner__chips { width: 100%; }
        }
        .btn-audit {
            color: #fff;
            background: linear-gradient(135deg, #6f42c1 0%, #4c2882 100%);
            box-shadow: 0 2px 8px rgba(111, 66, 193, .35);
        }
        .btn-audit:hover,
        .btn-audit:focus {
            color: #fff;
            filter: brightness(1.12);
            box-shadow: 0 4px 12px rgba(111, 66, 193, .45);
        }
        .btn-audit:disabled,
        .btn-audit.disabled {
            opacity: .65;
        }
    </style>
    @endpush
@endonce

@hasanyrole('admin|superadmin|SPV QC')
<div {{ $attributes->merge(['class' => 'audit-banner', 'role' => 'note', 'aria-label' => 'Informasi mode audit']) }}>
    <div class="d-flex align-items-center">
        <div class="audit-banner__icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <h5 class="audit-banner__title">
                <i class="fas fa-clipboard-check mr-1"></i> {{ $title }}
            </h5>
            <p class="audit-banner__text">
                @if ($message)
                    {{ $message }}
                @elseif ($slot->isNotEmpty())
                    {{ $slot }}
                @else
                    Perubahan di halaman ini hanya berlaku pada <strong>salinan data audit</strong>
                    untuk keperluan Audit QC System. Data operasional utama tidak terpengaruh.
                @endif
            </p>
        </div>
    </div>

    @if (!empty($chips))
        <div class="audit-banner__chips">
            @foreach ($chips as $chip)
                <span class="audit-banner__chip">
                    <i class="{{ $chip['icon'] }}"></i> {{ $chip['label'] }}
                </span>
            @endforeach
        </div>
    @endif
</div>
@endhasanyrole