@extends('layouts.app')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="page-header">

    <div>
        <h1>Edit Client</h1>
        <p>Update client information.</p>
    </div>

</div>

<div class="form-card">

    <form id="editClientForm">

        <div class="form-grid">

            <!-- FIRST NAME -->
            <div class="form-group">
                <label>First Name</label>
                <input type="text" id="first_name" required>
            </div>

            <!-- LAST NAME -->
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" id="last_name" required>
            </div>

            <!-- ADDRESS -->
            <div class="form-group">
                <label>Address</label>
                <input type="text" id="address" required>
            </div>

            <!-- TELEPHONE -->
            <div class="form-group">
                <label>Telephone</label>
                <input type="text" id="telephone_no" required>
            </div>

            <!-- PREFERRED TYPE -->
            <div class="form-group">
                <label>Preferred Type</label>
                <select id="preferred_type">
                    <option value="">Select Type</option>
                    <option value="Apartment">Apartment</option>
                    <option value="House">House</option>
                    <option value="Condo">Condo</option>
                    <option value="Flat">Flat</option>
                </select>
            </div>

            <!-- PREFERRED LOCATION -->
            <div class="form-group">
                <label>Preferred Location</label>
                <input type="text" id="preferred_location">
            </div>

            <!-- MAX RENT -->
            <div class="form-group">
                <label>Max Rent</label>
                <input type="number" id="max_rent" min="0">
            </div>

            <!-- BRANCH -->
            <div class="form-group">
                <label>Branch</label>
                <select id="branch_no">
                    <option value="">Select Branch</option>
                </select>
            </div>

            <!-- STAFF -->
            <div class="form-group">
                <label>Staff</label>
                <select id="staff_no">
                    <option value="">Select Branch first</option>
                </select>
            </div>

        </div>

        <div class="form-actions">
            <a href="/renter" class="cancel-btn">Cancel</a>
            <button type="submit" id="submitBtn" class="submit-btn">
                Update Client
            </button>
        </div>

    </form>

</div>

<script>

const renterId = window.location.pathname.split('/')[2];

/* ================= LOAD BRANCHES ================= */
async function loadBranches(selectedBranch = null) {

    try {
        const response = await fetch('/api/branches', {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const result = await response.json();
        const branches = result.data ?? [];

        const branchSelect = document.getElementById('branch_no');
        branchSelect.innerHTML = '<option value="">Select Branch</option>';

        branches.forEach(branch => {
            const label = 'B' + String(branch.branch_no).padStart(3, '0');
            branchSelect.innerHTML += `
                <option value="${branch.branch_no}"
                    ${selectedBranch == branch.branch_no ? 'selected' : ''}>
                    ${label} — ${branch.city}
                </option>
            `;
        });

    } catch (error) {
        console.error('loadBranches error:', error);
    }
}

/* ================= LOAD STAFF ================= */
async function loadStaff(branchNo, selectedStaff = null) {

    const staffSelect = document.getElementById('staff_no');
    staffSelect.innerHTML = '<option value="">Loading staff...</option>';
    staffSelect.disabled = true;

    if (!branchNo) {
        staffSelect.innerHTML = '<option value="">Select Branch first</option>';
        return;
    }

    try {
        const response = await fetch(`/api/branches/${branchNo}/staff`, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const result = await response.json();

        // getStaff returns a plain array; guard against {data:[]} shape too
        const staff = Array.isArray(result) ? result : (result.data ?? []);

        staffSelect.innerHTML = '<option value="">Select Staff</option>';

        if (staff.length === 0) {
            staffSelect.innerHTML = '<option value="">No staff in this branch</option>';
            return;
        }

        staff.forEach(member => {
            const staffNo   = 'S' + String(member.staff_no).padStart(3, '0');
            const staffName = member.full_name
                ?? (member.first_name + ' ' + member.last_name);

            staffSelect.innerHTML += `
                <option value="${member.staff_no}"
                    ${selectedStaff == member.staff_no ? 'selected' : ''}>
                    ${staffNo} — ${staffName}
                </option>
            `;
        });

        staffSelect.disabled = false;

    } catch (error) {
        console.error('loadStaff error:', error);
        staffSelect.innerHTML = '<option value="">Error loading staff</option>';
    }
}

/* ================= LOAD CLIENT ================= */
async function loadClient() {

    try {
        const response = await fetch(`/api/renters/${renterId}`, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const result = await response.json();
        const renter = result.data;

        document.getElementById('first_name').value        = renter.first_name        ?? '';
        document.getElementById('last_name').value         = renter.last_name         ?? '';
        document.getElementById('address').value           = renter.address           ?? '';
        document.getElementById('telephone_no').value      = renter.telephone_no      ?? '';
        document.getElementById('preferred_type').value    = renter.preferred_type    ?? '';
        document.getElementById('preferred_location').value = renter.preferred_location ?? '';
        document.getElementById('max_rent').value          = renter.max_rent          ?? '';

        await loadBranches(renter.branch_no);
        await loadStaff(renter.branch_no, renter.staff_no);

    } catch (error) {
        console.error('loadClient error:', error);
        alert('Failed to load client data.');
    }
}

/* ================= BRANCH CHANGE ================= */
document.getElementById('branch_no').addEventListener('change', function () {
    loadStaff(this.value);
});

/* ================= SUBMIT ================= */
document.getElementById('editClientForm').addEventListener('submit', async function (e) {

    e.preventDefault();

    const submitBtn       = document.getElementById('submitBtn');
    submitBtn.disabled    = true;
    submitBtn.textContent = 'Updating...';

    const payload = {
        first_name:         document.getElementById('first_name').value,
        last_name:          document.getElementById('last_name').value,
        address:            document.getElementById('address').value,
        telephone_no:       document.getElementById('telephone_no').value,
        preferred_type:     document.getElementById('preferred_type').value     || null,
        preferred_location: document.getElementById('preferred_location').value || null,
        max_rent:           document.getElementById('max_rent').value           || null,
        branch_no:          document.getElementById('branch_no').value,
        staff_no:           document.getElementById('staff_no').value,
    };

    try {
        const response = await fetch(`/api/renters/${renterId}`, {
            method:      'PUT',
            credentials: 'same-origin',
            headers: {
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (!response.ok) {
            alert(result.message || 'Failed to update client.');
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Update Client';
            return;
        }

        alert('Client updated successfully.');
        window.location.href = '/renter';

    } catch (error) {
        console.error('submit error:', error);
        alert('Network or server error. Please try again.');
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Update Client';
    }
});

/* ================= INIT ================= */
loadClient();

</script>

@endsection