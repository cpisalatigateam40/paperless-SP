@props(['label', 'value' => null])

<div class="fo-field">
    <div class="fo-label">{{ $label }}</div>
    <div class="fo-value text-break">
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @else
            {{ filled($value) ? $value : '-' }}
        @endif
    </div>
</div>