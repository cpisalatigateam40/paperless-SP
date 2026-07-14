@extends('layouts.app')

@section('title', 'Master Smoke House')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Master Smoke House</h5>

            <a href="{{ route('master-smoke-houses.create') }}"
                class="btn btn-primary">
                <i class="bx bx-plus"></i> Tambah
            </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Area</th>
                            <th>Product</th>
                            <th>Machine</th>
                            <th width="120">Total Step</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($masters as $item)

                        <tr>

                            <td class="text-center">
                                {{ $loop->iteration + ($masters->firstItem() - 1) }}
                            </td>

                            <td>{{ $item->area->name }}</td>

                            <td>{{ $item->product->product_name }}</td>

                            <td>{{ $item->machine_name }}</td>

                            <td class="text-center">
                                {{ $item->steps()->count() }}
                            </td>

                            <td class="text-center d-flex gap-2 justify-content-center  align-items-center" style="gap: .4rem;">

                                <button
                                    type="button"
                                    class="btn btn-info btn-sm btn-toggle">

                                    Lihat  

                                </button>

                                <a href="{{ route('master-smoke-houses.edit',$item->uuid) }}"
                                    class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <form
                                    action="{{ route('master-smoke-houses.destroy',$item->uuid) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        onclick="return confirm('Hapus data?')"
                                        class="btn btn-danger btn-sm">

                                        Hapus

                                    </button>
                                </form>
                            </td>
                        </tr>

                        <tr class="detail-row d-none">

                            <td colspan="6" class="bg-light">

                                <table class="table table-sm table-bordered mb-0">

                                    <thead>

                                        <tr>

                                            <th width="60">Seq</th>

                                            <th>Process</th>

                                            <th>Temp</th>

                                            <th>Time</th>

                                            <th>RH</th>

                                            <th>CT</th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        @foreach($item->steps as $step)

                                        <tr>

                                            <td class="text-center">
                                                {{ $step->sequence }}
                                            </td>

                                            <td>
                                                {{ $step->process_name }}
                                            </td>

                                            <td class="text-center">

                                                @if($step->temperature_min || $step->temperature_max)

                                                    {{ $step->temperature_min }}
                                                    -
                                                    {{ $step->temperature_max }}

                                                @else

                                                    -

                                                @endif

                                            </td>

                                            <td class="text-center">

                                                {{ $step->time_minutes ?? '-' }}

                                            </td>

                                            <td class="text-center">

                                                {{ $step->rh ?? '-' }}

                                            </td>

                                            <td class="text-center">

                                                {{ $step->core_temperature ?? '-' }}

                                            </td>

                                        </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </td>

                        </tr>
                    @empty

                        <tr>
                            <td colspan="6" class="text-center">
                                Tidak ada data.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>
            </div>

            <div class="mt-3">
                {{ $masters->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

<script>

document.querySelectorAll('.btn-toggle').forEach(function(btn){

    btn.addEventListener('click', function(){

        let detail = this.closest('tr').nextElementSibling;

        detail.classList.toggle('d-none');

        let icon = this.querySelector('i');

        if(detail.classList.contains('d-none')){

            icon.classList.remove('bx-chevron-up');
            icon.classList.add('bx-chevron-down');

        }else{

            icon.classList.remove('bx-chevron-down');
            icon.classList.add('bx-chevron-up');

        }

    });

});

</script>

@endsection