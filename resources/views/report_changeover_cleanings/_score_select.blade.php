@php
    $selected = $selected ?? null;
    $name = $name ?? null;
@endphp
@if($name)
<select name="{{ $name }}" class="form-control form-control-sm">
    <option value="">-</option>
    @foreach($criteriaPairs as $pair)
        @foreach($pair as $num)
        <option value="{{ $num }}" @selected($selected == $num)>
            {{ $num }} - {{ $criteria[$num] }}
        </option>
        @endforeach
    @endforeach
</select>
@endif