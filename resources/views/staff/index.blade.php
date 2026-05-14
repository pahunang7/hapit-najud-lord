@extends('layouts.app')
@section('content')

<div class="page-header">
    <div>
        <h1>Staff List</h1>
        <p>All staff across DreamHome branches</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('staff.supervisor.list') }}" class="cancel-btn">Supervisors</a>
        <a href="{{ route('staff.create') }}" class="add-btn">+ Add Staff</a>
    </div>
</div>

{{-- SEARCH + FILTERS --}}
<div class="table-card search-card">
    <form method="GET" action="{{ route('staff.index') }}" class="search-form">
        <input
            type="text"
            name="search"
            placeholder="Search by name or NIN..."
            value="{{ request('search') }}"
        >
        <select name="branch_no">
            <option value="">All Branches</option>
            @foreach(\App\Models\BranchOffice::orderBy('city')->get() as $branch)
                <option value="{{ $branch->branch_no }}"
                    {{ request('branch_no') == $branch->branch_no ? 'selected' : '' }}>
                    {{ 'B' . str_pad($branch->branch_no, 3, '0', STR_PAD_LEFT) }} — {{ $branch->city }}
                </option>
            @endforeach
        </select>
        <select name="job_title">
            <option value="">All Job Titles</option>
            <option value="Manager"    {{ request('job_title') == 'Manager'    ? 'selected' : '' }}>Manager</option>
            <option value="Supervisor" {{ request('job_title') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
            <option value="Secretary"  {{ request('job_title') == 'Secretary'  ? 'selected' : '' }}>Secretary</option>
            <option value="Staff"      {{ request('job_title') == 'Staff'      ? 'selected' : '' }}>Staff</option>
        </select>
        <button type="submit">Search</button>
        <a href="{{ route('staff.index') }}" class="cancel-btn">Clear</a>
    </form>
</div>

@if(session('success'))
    <div class="success-message">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="error-message">{{ session('error') }}</div>
@endif

{{-- TABLE --}}
<div class="table-card">
    <div style="overflow-x: auto;">
        <table class="client-table" style="min-width: 1200px;">
            <thead>
                <tr>
                    <th>Staff No</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Address</th>
                    <th>Telephone</th>
                    <th>Sex</th>
                    <th>Date of Birth</th>
                    <th>NIN</th>
                    <th>Job Title</th>
                    <th>Salary</th>
                    <th>Date Joined</th>
                    <th>Branch</th>
                    <th>Supervisor</th>
                    <th>Manager Details</th>
                    <th>Secretary Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $member)
                <tr>
                    <td>{{ 'S' . str_pad($member->staff_no, 3, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $member->first_name }}</td>
                    <td>{{ $member->last_name }}</td>
                    <td>{{ $member->address }}</td>
                    <td>{{ $member->telephone_no }}</td>
                    <td>{{ $member->sex ?? '-' }}</td>
                    <td>{{ $member->date_of_birth }}</td>
                    <td>{{ $member->nin }}</td>
                    <td>
                        @php
                            $chipClass = match($member->job_title) {
                                'Manager'    => 'chip-manager',
                                'Supervisor' => 'chip-supervisor',
                                'Secretary'  => 'chip-secretary',
                                default      => 'chip-staff',
                            };
                        @endphp
                        <span class="chip {{ $chipClass }}">{{ $member->job_title }}</span>
                    </td>
                    <td>{{ number_format($member->salary, 2) }}</td>
                    <td>{{ $member->date_joined }}</td>
                    <td>
                        {{ $member->branch
                            ? 'B' . str_pad($member->branch->branch_no, 3, '0', STR_PAD_LEFT) . ' — ' . $member->branch->city
                            : '-' }}
                    </td>
                    <td>
                        {{ $member->supervisor
                            ? 'S' . str_pad($member->supervisor->staff_no, 3, '0', STR_PAD_LEFT) . ' — ' . $member->supervisor->first_name . ' ' . $member->supervisor->last_name
                            : '-' }}
                    </td>
                    <td>
                        @if($member->job_title === 'Manager')
                            <small>
                                Date Start: {{ $member->date_start ?? '-' }}<br>
                                Car Allowance: {{ $member->car_allowance ? number_format($member->car_allowance, 2) : '-' }}<br>
                                Bonus: {{ $member->bonus ? number_format($member->bonus, 2) : '-' }}
                            </small>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($member->job_title === 'Secretary')
                            <small>Typing Speed: {{ $member->typing_speed ?? '-' }} WPM</small>
                        @else
                            -
                        @endif
                    </td>
                    <td class="action-buttons">
                        <a href="{{ route('staff.show', $member->staff_no) }}" class="view-btn">View</a>
                        <a href="{{ route('staff.edit', $member->staff_no) }}" class="edit-btn">Edit</a>
                        <button
                            class="delete-btn"
                            onclick="deleteStaff({{ $member->staff_no }}, this)"
                        >Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="16" style="text-align:center;">No staff found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrapper">
    {{ $staff->appends(request()->query())->links() }}
</div>

<script>
async function deleteStaff(staffId, btn) {
    if (!confirm('Delete this staff member? This cannot be undone.')) return;

    btn.disabled = true;
    btn.textContent = 'Deleting...';

    try {
        const response = await fetch(`/api/staff/${staffId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const result = await response.json();

        if (!result.success) {
            alert(result.message || 'Could not delete staff member.');
            btn.disabled = false;
            btn.textContent = 'Delete';
            return;
        }

        btn.closest('tr').remove();

    } catch (error) {
        console.error(error);
        alert('Something went wrong.');
        btn.disabled = false;
        btn.textContent = 'Delete';
    }
}
</script>

@endsection