@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<div class="page-content">

    <div class="page-header">
        <div>
            <h1>Branch Staff</h1>
            <p>All staff assigned to this branch.</p>
        </div>
        <a href="/branches/{{ $branchOffice->branch_no }}" class="cancel-btn">← Back to Branch</a>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <h2>Staff Members</h2>
            <span class="count-badge" id="staffCount">Loading...</span>
        </div>
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
            <tbody id="staffTableBody">
                <tr>
                    <td colspan="5" style="text-align:center; color:#9aa0ac; padding:30px;">
                        Loading...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
function chipClass(role) {
    const map = {
        'Manager':    'chip-manager',
        'Supervisor': 'chip-supervisor',
        'Secretary':  'chip-secretary',
        'Staff':      'chip-staff',
    };
    return 'chip ' + (map[role] || 'chip-staff');
}

async function loadStaff() {
    try {
        const response = await fetch('/api/staff?branch_no={{ $branchOffice->branch_no }}');
        const result   = await response.json();

        const tableBody  = document.getElementById('staffTableBody');
        const countBadge = document.getElementById('staffCount');

        if (!result.success) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align:center; color:#9aa0ac; padding:30px;">
                        Failed to load staff.
                    </td>
                </tr>`;
            countBadge.textContent = '0 members';
            return;
        }

        const staff = result.data;
        countBadge.textContent = staff.length + (staff.length === 1 ? ' member' : ' members');

        if (staff.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align:center; color:#9aa0ac; padding:30px;">
                        No staff found for this branch.
                    </td>
                </tr>`;
            return;
        }

        tableBody.innerHTML = staff.map(member => {
            const staffNo = 'S' + String(member.staff_no).padStart(3, '0');
            const salary  = member.salary
                ? '£' + parseFloat(member.salary).toLocaleString('en-GB', { minimumFractionDigits: 2 })
                : '—';

            return `
                <tr>
                    <td><span class="staff-no">${staffNo}</span></td>
                    <td>${member.first_name} ${member.last_name}</td>
                    <td><span class="${chipClass(member.job_title)}">${member.job_title}</span></td>
                    <td>${member.telephone_no}</td>
                    <td>${salary}</td>
                </tr>`;
        }).join('');

    } catch (error) {
        console.error(error);
        document.getElementById('staffTableBody').innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center; color:#9aa0ac; padding:30px;">
                    Error loading staff. Please try again.
                </td>
            </tr>`;
        document.getElementById('staffCount').textContent = '—';
    }
}

loadStaff();
</script>

@endsection