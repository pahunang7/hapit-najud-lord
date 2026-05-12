@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<div class="page-content">

    <div class="page-header">
        <div>
            <h1>Branch Report</h1>
            <p>Complete overview of branch performance and staffing.</p>
        </div>
        <a href="/branches/{{ $branchOffice->branch_no }}" class="cancel-btn">← Back to Branch</a>
    </div>

    {{-- BRANCH DETAILS --}}
    <div class="table-card padded">
        <div class="table-card-header">
            <h2>Branch Information</h2>
            <span class="count-badge">{{ 'B' . str_pad($branchOffice->branch_no, 3, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="detail-body">
            <h3>Location</h3>
            <p><strong>Street:</strong> {{ $branchOffice->street }}</p>
            <p><strong>Area:</strong> {{ $branchOffice->area ?? '—' }}</p>
            <p><strong>City:</strong> {{ $branchOffice->city }}</p>
            <p><strong>Postcode:</strong> {{ $branchOffice->postcode }}</p>
            <hr>
            <h3>Contact</h3>
            <p><strong>Telephone:</strong> {{ $branchOffice->telephone_no }}</p>
            <p><strong>Fax:</strong> {{ $branchOffice->fax_no ?? '—' }}</p>
        </div>
    </div>

    {{-- SUMMARY --}}
    <div class="table-card padded">
        <div class="table-card-header">
            <h2>Summary</h2>
        </div>
        <div class="detail-body">
            <p>
                <strong>Total Staff:</strong>
                <span class="count-badge" style="margin-left:8px;">{{ $staffCount }}</span>
            </p>
            <p>
                <strong>Manager:</strong>
                @if($manager)
                    <span class="chip chip-manager" style="margin-left:8px;">
                        {{ 'S' . str_pad($manager->staff_no, 3, '0', STR_PAD_LEFT) }}
                        — {{ $manager->first_name }} {{ $manager->last_name }}
                    </span>
                @else
                    <span class="text-muted" style="margin-left:8px;">None assigned</span>
                @endif
            </p>
        </div>
    </div>

    {{-- SUPERVISORS --}}
    <div class="table-card">
        <div class="table-card-header">
            <h2>Supervisors</h2>
            <span class="count-badge">{{ count($supervisors) }} {{ Str::plural('supervisor', count($supervisors)) }}</span>
        </div>
        <table class="client-table">
            <thead>
                <tr>
                    <th>Staff No</th>
                    <th>Name</th>
                    <th>Subordinates</th>
                </tr>
            </thead>
            <tbody>
            @forelse($supervisors as $sup)
                <tr>
                    <td><span class="staff-no">{{ 'S' . str_pad($sup->staff_no, 3, '0', STR_PAD_LEFT) }}</span></td>
                    <td>{{ $sup->first_name }} {{ $sup->last_name }}</td>
                    <td>
                        <span class="count-badge">{{ $sup->subordinates->count() }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:#9aa0ac; padding:30px;">
                        No supervisors in this branch.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ALL STAFF --}}
    <div class="table-card">
        <div class="table-card-header">
            <h2>All Staff</h2>
            <span class="count-badge">{{ $staffCount }} {{ Str::plural('member', $staffCount) }}</span>
        </div>
        <table class="client-table">
            <thead>
                <tr>
                    <th>Staff No</th>
                    <th>Name</th>
                    <th>Job Title</th>
                    <th>Supervisor</th>
                    <th>Telephone</th>
                </tr>
            </thead>
            <tbody>
            @forelse($staff as $member)
                @php
                    $chipClass = match($member->job_title) {
                        'Manager'    => 'chip-manager',
                        'Supervisor' => 'chip-supervisor',
                        'Secretary'  => 'chip-secretary',
                        default      => 'chip-staff',
                    };
                @endphp
                <tr>
                    <td><span class="staff-no">{{ 'S' . str_pad($member->staff_no, 3, '0', STR_PAD_LEFT) }}</span></td>
                    <td>{{ $member->first_name }} {{ $member->last_name }}</td>
                    <td><span class="chip {{ $chipClass }}">{{ $member->job_title }}</span></td>
                    <td>
                        @if($member->supervisor)
                            <span class="staff-no">{{ 'S' . str_pad($member->supervisor->staff_no, 3, '0', STR_PAD_LEFT) }}</span>
                            <span class="staff-name-sub">{{ $member->supervisor->first_name }} {{ $member->supervisor->last_name }}</span>
                        @else
                            <span class="text-muted">None</span>
                        @endif
                    </td>
                    <td>{{ $member->telephone_no }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#9aa0ac; padding:30px;">
                        No staff found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection