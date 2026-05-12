@extends('layouts.app')
@section('content')

<div class="page-header">
    <div>
        <h1>Supervisor Team</h1>
        <p>
            Team under:
            {{ 'S' . str_pad($supervisor->staff_no, 3, '0', STR_PAD_LEFT) }} —
            {{ $supervisor->first_name }} {{ $supervisor->last_name }}
        </p>
    </div>
    <a href="{{ route('supervisor.list') }}" class="cancel-btn">← Back to Supervisors</a>
</div>

<div class="table-card">
    <table class="client-table">
        <thead>
            <tr>
                <th>Staff No</th>
                <th>Name</th>
                <th>Job Title</th>
                <th>Telephone</th>
                <th>Salary</th>
            </tr>
        </thead>
        <tbody>
        @forelse($supervisor->subordinates as $staff)
            <tr>
                <td>{{ 'S' . str_pad($staff->staff_no, 3, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $staff->first_name }} {{ $staff->last_name }}</td>
                <td>{{ $staff->job_title }}</td>
                <td>{{ $staff->telephone_no }}</td>
                <td>{{ $staff->salary ? number_format($staff->salary, 2) : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;">No subordinates found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection