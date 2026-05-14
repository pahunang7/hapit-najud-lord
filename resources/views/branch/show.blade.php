@extends('layouts.app')
@section('content')


<link rel="stylesheet" href="{{ asset('css/style.css') }}">

async function loadBranch() {
    try {
        const response = await fetch(`/api/branches/${branchId}`, {
            headers: {
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

<div class="page-header">
    <div>
        <h1>Branch Details</h1>
        <p>View branch information, staff count, manager, and staff list access</p>
    </div>
</div>

<div class="table-card" id="branchContainer">
    Loading branch details...
</div>

<script>
const branchId = "{{ $branchOffice->branch_no }}";

function formatBranchNo(no) {
    return 'B' + String(no).padStart(3, '0');
}

function formatStaffNo(no) {
    return 'S' + String(no).padStart(3, '0');
}

async function loadBranch() {
    try {
        const response = await fetch(`/api/branches/${branchId}`);
        const result = await response.json();

        const container = document.getElementById('branchContainer');

        if (!result.success) {
            container.innerHTML = "Failed to load branch details";
            return;
        }

        const data = result.data;

        container.innerHTML = `
            <div class="branch-details">

                <h2>Branch ${formatBranchNo(data.branch.branch_no)}</h2>

                <p><strong>Branch No:</strong> ${formatBranchNo(data.branch.branch_no)}</p>
                <p><strong>Street:</strong> ${data.branch.street}</p>
                <p><strong>Area:</strong> ${data.branch.area ?? '-'}</p>
                <p><strong>City:</strong> ${data.branch.city}</p>
                <p><strong>Postcode:</strong> ${data.branch.postcode}</p>
                <p><strong>Telephone:</strong> ${data.branch.telephone_no}</p>
                <p><strong>Fax:</strong> ${data.branch.fax_no ?? '-'}</p>

                <hr>

                <p><strong>Staff Count:</strong> ${data.staff_count}</p>

                <p><strong>Manager:</strong>
                    ${data.manager
                        ? formatStaffNo(data.manager.staff_no) + ' — ' + data.manager.first_name + ' ' + data.manager.last_name
                        : 'No Manager Assigned'}
                </p>

                <hr>

                <a href="/branches/${data.branch.branch_no}/staff" class="add-btn">
                    View Staff
                </a>

                <a href="/branches/${data.branch.branch_no}/report" class="edit-btn" style="margin-left:10px;">
                    View Report
                </a>

            </div>
        `;

    } catch (error) {
        console.error(error);
        document.getElementById('branchContainer').innerHTML = "Error loading branch details";
    }
}

loadBranch();
</script>

@endsection