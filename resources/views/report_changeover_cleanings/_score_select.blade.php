@php
    $selected = $selected ?? [];        // sekarang array, bukan single value
    $selected = is_array($selected) ? $selected : (array) $selected;
    $name = $name ?? null;
    $minCriteria = $minCriteria ?? 1;
    $safeId = $name ? preg_replace('/[^A-Za-z0-9_]/', '_', $name) : null;
@endphp
@if($name)
    @foreach($criteriaPairs as $pair)
        @php $disabled = $pair[0] < $minCriteria; @endphp
        <td class="text-center align-middle {{ $disabled ? 'bg-secondary bg-opacity-25' : '' }}" style="min-width:70px;">
            @if(!$disabled)
                @foreach($pair as $num)
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox"
                               name="{{ $name }}[]"
                               id="{{ $safeId }}_{{ $num }}"
                               value="{{ $num }}"
                               title="{{ $criteria[$num] ?? '' }}"
                               @checked(in_array((string) $num, array_map('strval', $selected)))>
                        <label class="form-check-label small" for="{{ $safeId }}_{{ $num }}">{{ $num }}</label>
                    </div>
                @endforeach
            @endif
        </td>
    @endforeach
@else
    <td colspan="4"></td>
@endif