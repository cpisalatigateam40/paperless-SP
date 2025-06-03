@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="d-flex justify-content-between align-items-center">
                Data Role
                <a href="{{route('roles.create')}}" class="btn btn-primary btn-sm">+</a>
            </h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div id="error-alert" class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>No.</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <th>{{$loop->iteration}}</th>
                        <td>{{$role->name}}</td>
                        <td>
                            <div class="d-flex" style="gap: .5rem;">
                                <a href="{{ route('roles.manage-access', $role->id) }}" class="btn btn-info btn-sm">
                                Manage Access
                                </a>

                                <a href="{{route('roles.edit', $role->id)}}" class="btn btn-warning btn-sm">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteRoleModal{{$role->id}}">
                                    Delete
                                </button>
                            </div>
                            

                            <div class="modal fade" id="deleteRoleModal{{$role->id}}" tabindex="-1" aria-labelledby="deleteRoleModalLabel{{$role->id}}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deletePlantRoleLabel{{$role->id}}">Hapus Plant</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Anda yakin menghapus role ini?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
        setTimeout(() => {
            $('#success-alert').fadeOut('slow');
            $('#error-alert').fadeOut('slow');
        }, 3000);
    });
    </script>
@endsection