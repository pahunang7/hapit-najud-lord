@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<div class="page-content">

    <div class="page-header">
        <div>
            <h1>Register Client</h1>
            <p>Register a new client looking for a property.</p>
        </div>
        <a href="/renter" class="cancel-btn">← Back</a>
    </div>

    <div id="formMessage"></div>

    <div class="form-card">
        <h3>Client Information</h3>

        <form id="registerClientForm">
            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" id="first_name" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" id="last_name" required>
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" id="address" required>
                </div>

                <div class="form-group">
                    <label>Telephone</label>
                    <input type="text" id="telephone_no" required>
                </div>

                <div class="form-group">
                    <label>Preferred Type</label>
                    <select id="preferred_type">
                        <option value="">Select Type</option>
                        <option value="Apartment">Apartment</option>
                        <option value="House">House</option>
                        <option value="Flat">Flat</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Preferred Location</label>
                    <input type="text" id="preferred_location">
                </div>

                <div class="form-group">
                    <label>Max Rent</label>
                    <input type="number" id="max_rent" min="0">
                </div>

                <div class="form-group">
                    <label>Branch</label>
                    <select id="branch_no" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->branch_no }}">
                                B{{ str_pad($branch->branch_no, 3, '0', STR_PAD_LEFT) }}
                                — {{ $branch->city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Staff</label>
                    <select id="staff_no" required disabled>
                        <option value="">Select Branch first</option>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <a href="/renter" class="cancel-btn">Cancel</a>
                <button type="submit" id="submitBtn" class="submit-btn">
                    Register Client
                </button>
            </div>

        </form>
    </div>

</div>

<script>
/* =========================================================
   LOAD STAFF — uses /api/branches/{branchNo}/staff
   BranchOfficeController::getStaff returns a plain array,
   so we handle both array and {data:[]} shapes safely.
========================================================= */
document.getElementById('branch_no').addEventListener('change', async function () {

    const branchNo  = this.value;
    const staffSelect = document.getElementById('staff_no');

    staffSelect.innerHTML  = '<option value="">Loading staff...</option>';
    staffSelect.disabled   = true;

    if (!branchNo) {
        staffSelect.innerHTML = '<option value="">Select Branch first</option>';
        return;
    }

    try {
        const response = await fetch(`/api/branches/${branchNo}/staff`, {
            credentials: 'same-origin',
            headers: {
                'Accept':            'application/json',
                'X-Requested-With':  'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            staffSelect.innerHTML = '<option value="">Failed to load staff</option>';
            return;
        }

        const result = await response.json();

        // getStaff returns a plain array; guard against {data:[]} shape too
        const staff = Array.isArray(result) ? result : (result.data ?? []);

        staffSelect.innerHTML = '<option value="">Select Staff</option>';

        if (staff.length === 0) {
            staffSelect.innerHTML = '<option value="">No staff in this branch</option>';
            return;
        }

        staff.forEach(member => {
            // Support both full_name and separate first/last name fields
            const staffNo   = 'S' + String(member.staff_no).padStart(3, '0');
            const staffName = member.full_name
                ?? (member.first_name + ' ' + member.last_name);

            staffSelect.innerHTML += `
                <option value="${member.staff_no}">
                    ${staffNo} — ${staffName}
                </option>
            `;
        });

        staffSelect.disabled = false;

    } catch (err) {
        console.error(err);
        staffSelect.innerHTML = '<option value="">Error loading staff</option>';
    }
});

/* =========================================================
   FORM SUBMIT — posts to the web route POST /renter
   (handles all roles including Staff "Add Only")
========================================================= */
document.getElementById('registerClientForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const submitBtn       = document.getElementById('submitBtn');
    submitBtn.disabled    = true;
    submitBtn.textContent = 'Registering...';

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
        const response = await fetch('/renter', {
            method:      'POST',
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
            showMessage(result.message || 'Failed to register client.', 'error');
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Register Client';
            return;
        }

        showMessage('Client registered successfully!', 'success');
        setTimeout(() => window.location.href = '/renter', 1000);

    } catch (error) {
        console.error(error);
        showMessage('Network or server error. Please try again.', 'error');
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Register Client';
    }
});

/* =========================================================
   MESSAGE HANDLER
========================================================= */
function showMessage(msg, type) {
    const div       = document.getElementById('formMessage');
    div.className   = type === 'success' ? 'success-message' : 'error-message';
    div.textContent = msg;
    div.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

@endsection