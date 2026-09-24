@if($status === 'completed')
    <span class="status-badge-completed">COMPLETED</span>
@elseif($status === 'running')
    <span class="status-badge-running">RUNNING</span>
@else
    <span class="status-badge-waiting">WAITING</span>
@endif