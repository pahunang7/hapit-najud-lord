@extends('layouts.app')
@section('content')

<div class="page-header">
    <div>
        <h1>Supervisors</h1>
        <p>List of all supervisors and their team size</p>
    </div>
</div>

<div class="table-card">
    <table class="client-table">
        <thead>
            <tr>
                <th>Staff No</th>
                <th>Name</th>
                <th>Branch</th>
                <th>Subordinates Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        {{-- ✅ FIXED: $supervisors not $staff --}}
        @forelse($supervisors as $sup)
            <tr>
                <td>{{ 'S' . str_pad($sup->staff_no, 3, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $sup->first_name }} {{ $sup->last_name }}</td>
                {{-- ✅ FIXED: show branch city not raw branch_no --}}
                <td>
                    {{ $sup->branch
                        ? 'B' . str_pad($sup->branch->branch_no, 3, '0', STR_PAD_LEFT) . ' — ' . $sup->branch->city
                        : '-' }}
                </td>
                <td>{{ $sup->db_staff_count ?? $sup->subordinates->count() }}</td>
                <td>
                    <a href="{{ route('staff.subordinates', $sup->staff_no) }}" class="view-btn">
                        View Team
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;">No supervisors found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection