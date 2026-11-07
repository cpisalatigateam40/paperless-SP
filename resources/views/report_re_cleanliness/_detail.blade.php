@php
    $cols = $cols ?? 7;
    $fmt  = fn ($t) => $t ? substr($t, 0, 5) : null;

    $times = $report->roomDetails->concat($report->equipmentDetails)
        ->map(fn ($d) => $fmt($d->verification_time))
        ->unique()->sort()->values();
@endphp

<tr id="detail-{{ $report->uuid }}" style="display: none;">
    <td colspan="{{ $cols }}" class="bg-light p-3">

        @forelse ($times as $time)
            @php
                $rooms = $report->roomDetails->filter(fn ($d) => $fmt($d->verification_time) === $time);
                $equipments = $report->equipmentDetails->filter(fn ($d) => $fmt($d->verification_time) === $time);
                $all = $rooms->concat($equipments);
                $cleanCount = $all->where('condition', 'clean')->count();
                $dirtyCount = $all->where('condition', 'dirty')->count();

                $sections = [
                    [
                        'title'   => 'Pemeriksaan Ruangan',
                        'details' => $rooms,
                        'group'   => fn ($d) => optional($d->room)->name ?? '-',
                        'item'    => fn ($d) => optional($d->element)->element_name,
                        'whole'   => 'Ruangan secara keseluruhan',
                        'col'     => 'Elemen',
                    ],
                    [
                        'title'   => 'Pemeriksaan Mesin & Peralatan',
                        'details' => $equipments,
                        'group'   => fn ($d) => optional($d->equipment)->name ?? '-',
                        'item'    => fn ($d) => optional($d->part)->part_name,
                        'whole'   => 'Mesin / peralatan secara keseluruhan',
                        'col'     => 'Part',
                    ],
                ];
            @endphp

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-primary fs-6">
                        <i class="bi bi-clock"></i> {{ $time ? $time . ' WIB' : 'Tanpa jam' }}
                    </span>
                    <span class="text-muted small">{{ $all->count() }} item diperiksa</span>
                    <span class="ms-auto">
                        <span class="badge bg-success">{{ $cleanCount }} bersih</span>
                        <span class="badge bg-{{ $dirtyCount ? 'danger' : 'secondary' }}">{{ $dirtyCount }} kotor</span>
                    </span>
                </div>

                <div class="card-body">
                    @foreach ($sections as $s)
                        @continue($s['details']->isEmpty())

                        <h6 class="fw-bold mb-2">{{ $s['title'] }}</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th style="width:50px">No</th>
                                        <th class="text-start">{{ $s['col'] }}</th>
                                        <th style="width:90px">Kondisi</th>
                                        <th>Keterangan</th>
                                        <th>Tindakan Koreksi</th>
                                        <th style="width:150px">Verifikasi Setelah Koreksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($s['details']->groupBy($s['group']) as $groupName => $details)
                                        <tr class="table-primary">
                                            <td class="text-center fw-bold">{{ $loop->iteration }}</td>
                                            <td colspan="5" class="fw-bold text-uppercase">{{ $groupName }}</td>
                                        </tr>

                                        @foreach ($details as $detail)
                                            @php $itemName = $s['item']($detail); @endphp
                                            <tr class="{{ $detail->condition === 'dirty' ? 'table-warning' : '' }}">
                                                <td></td>
                                                <td>
                                                    @if ($itemName)
                                                        {{ $itemName }}
                                                    @else
                                                        <em class="text-muted">{{ $s['whole'] }}</em>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($detail->condition === 'clean')
                                                        <span class="badge bg-success">Bersih</span>
                                                    @else
                                                        <span class="badge bg-danger">Kotor</span>
                                                    @endif
                                                </td>
                                                <td>{{ $detail->notes ?: '-' }}</td>
                                                <td>{{ $detail->corrective_action ?: '-' }}</td>
                                                <td class="text-center">
                                                    @if ($detail->verification === 'OK')
                                                        <span class="badge bg-success">OK</span>
                                                    @elseif ($detail->verification)
                                                        <span class="badge bg-danger">{{ $detail->verification }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>

                                            @foreach ($detail->followups as $i => $f)
                                                <tr class="table-secondary small">
                                                    <td></td>
                                                    <td colspan="2" class="text-end text-muted">↳ Koreksi lanjutan #{{ $i + 1 }}</td>
                                                    <td>{{ $f->notes ?: '-' }}</td>
                                                    <td>{{ $f->corrective_action ?: '-' }}</td>
                                                    <td class="text-center">
                                                        @if ($f->verification === 'OK')
                                                            <span class="badge bg-success">OK</span>
                                                        @elseif ($f->verification)
                                                            <span class="badge bg-danger">{{ $f->verification }}</span>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-3">Belum ada detail pemeriksaan.</div>
        @endforelse

    </td>
</tr>