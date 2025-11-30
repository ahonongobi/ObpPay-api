{{-- ------------------------------- --}}
{{-- LOGS ADMIN --}}
{{-- ------------------------------- --}}
 <style>
    /* -------------------------------------------
   TEXT MUTED FIX (Bootstrap .text-muted)
------------------------------------------- */

/* Light mode muted */
.text-muted {
    color: #6c757d !important;
}

/* Dark mode muted */
body.dark-mode .text-muted {
    color: #9ca3af !important;
}
 </style>


@extends('admin.layout')

@section('content')
<div class="settings-card shadow-lg">

    <div class="section-title text-secondary">Historique des actions administratives</div>

    <div class="table-card mt-3">
        <table class="table-modern w-100">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Type</th>
                    <th>Détails</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->admin->name }}</td>
                    <td>{{ $log->action }}</td>
                    <td>
                        <span class="badge bg-info text-dark">{{ ucfirst($log->type) }}</span>
                    </td>
                    <td>
                        @if($log->details)
                            <small class="text-muted">{{ json_encode($log->details) }}</small>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
            {{ $logs->links('vendor.pagination.custom') }}
        </div>
    </div>

</div>

@endsection


