@if($status === 'completed')
    <span class="tracking-status tracking-completed">
        <i class="bi bi-check-circle-fill"></i> Completed
    </span>
    @if($time ?? null)
        <span class="tracking-time-sep">|</span>
        <span class="tracking-time"><i class="bi bi-clock"></i> {{ $time->format('H.i') }}</span>
    @endif
@elseif($status === 'running')
    <span class="tracking-status tracking-running">
        <i class="bi bi-circle-fill"></i> Running
    </span>
    @if($time ?? null)
        <span class="tracking-time-sep">|</span>
        <span class="tracking-time"><i class="bi bi-clock"></i> {{ $time->format('H.i') }}</span>
    @endif
@else
    <span class="tracking-status tracking-pending">
        <i class="bi bi-circle"></i> Pending
    </span>
@endif