@extends('layouts.app')
@section('content')

<div class="page-header">
    <div>
        <h1>Staff Details</h1>
        <p>View full staff information</p>
    </div>
</div>

<div class="table-card" id="staffContainer">
    Loading staff details...
</div>

<script>
const staffId = window.location.pathname.split('/')[2];

function formatStaffNo(no) {
    return 'S' + String(no).padStart(3, '0');
}

function formatBranchNo(no) {
    return 'B' + String(no).padStart(3, '0');
}

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
        // ✅ FIXED: /api/staff/ not /staff/
        const response = await fetch(`/api/staff/${staffId}`);
        const result = await response.json();

        if (!result.success) {
            document.getElementById('staffContainer').innerHTML =
                '<p>Error loading staff details</p>';
            return;
        }

        const s = result.data;

        document.getElementById('staffContainer').innerHTML = `
            <div class="branch-details">

                <h2>${s.first_name} ${s.last_name}</h2>
                <p><strong>Staff No:</strong> ${formatStaffNo(s.staff_no)}</p>
                <p><strong>Job Title:</strong> <span class="chip ${chipClass(s.job_title)}">${s.job_title}</span></p>
                <p><strong>Branch:</strong> ${formatBranchNo(s.branch_no)}</p>
                <p><strong>Address:</strong> ${s.address}</p>
                <p><strong>Telephone:</strong> ${s.telephone_no}</p>
                <p><strong>Date of Birth:</strong> ${s.date_of_birth}</p>
                <p><strong>Date Joined:</strong> ${s.date_joined}</p>
                <p><strong>Sex:</strong> ${s.sex ?? '-'}</p>
                <p><strong>NIN:</strong> ${s.nin}</p>
                <p><strong>Salary:</strong> ${parseFloat(s.salary).toLocaleString('en-GB', { minimumFractionDigits: 2 })}</p>

                <hr>

                <p><strong>Supervisor:</strong>
                    ${s.supervisor
                        ? formatStaffNo(s.supervisor.staff_no) + ' — ' + s.supervisor.first_name + ' ' + s.supervisor.last_name
                        : 'None'}
                </p>

                ${s.job_title === 'Manager' ? `
                <hr>
                <h3>Manager Details</h3>
                <p><strong>Date Start:</strong> ${s.date_start ?? '-'}</p>
                <p><strong>Car Allowance:</strong> ${s.car_allowance ?? '-'}</p>
                <p><strong>Bonus:</strong> ${s.bonus ?? '-'}</p>
                ` : ''}

                ${s.job_title === 'Secretary' ? `
                <hr>
                <h3>Secretary Details</h3>
                <p><strong>Typing Speed:</strong> ${s.typing_speed ?? '-'} WPM</p>
                ` : ''}

                <hr>

                <h3>Next of Kin</h3>
                <p><strong>Name:</strong> ${s.next_of_kin?.full_name ?? 'N/A'}</p>
                <p><strong>Relationship:</strong> ${s.next_of_kin?.relationship ?? 'N/A'}</p>
                <p><strong>Address:</strong> ${s.next_of_kin?.address ?? 'N/A'}</p>
                <p><strong>Phone:</strong> ${s.next_of_kin?.telephone_no ?? 'N/A'}</p>

                <hr>

                <div style="margin-top:15px;">
                    <a href="/staff/${s.staff_no}/edit" class="edit-btn">Edit</a>
                    <a href="/staff" class="cancel-btn">Back</a>
                </div>

            </div>
        `;

    } catch (error) {
        console.error(error);
        document.getElementById('staffContainer').innerHTML =
            '<p>Error loading staff details</p>';
    }
}

loadStaff();
</script>

@endsection