@if($status === 'completed')
    <span class="tracking-status tracking-completed">
        <i class="bi bi-check-circle-fill"></i> Completed
    </span>
@elseif($status === 'running')
    <span class="tracking-status tracking-running">
        <i class="bi bi-circle-fill"></i> Running
    </span>
@else
    <span class="tracking-status tracking-pending">
        <i class="bi bi-circle"></i> Pending
    </span>
@endif