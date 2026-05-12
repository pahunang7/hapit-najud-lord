@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<div class="page-content">

    <div class="page-header">
        <div>
            <h1>Create Staff</h1>
            <p>Add a new staff member.</p>
        </div>
        <a href="/staff" class="cancel-btn">← Back</a>
    </div>

    <div id="formMessage"></div>

    <div class="form-card">
        <form id="staffForm">
            @csrf

            {{-- ========================= BASIC INFO ========================= --}}
            <h3>Basic Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" id="first_name" placeholder="e.g. John" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" id="last_name" placeholder="e.g. Smith" required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" id="address" placeholder="e.g. 18 Deer Rd, Glasgow" required>
                </div>
                <div class="form-group">
                    <label>Telephone No.</label>
                    <input type="text" id="telephone_no" placeholder="e.g. 0141-334-5677" required>
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" id="date_of_birth" required>
                </div>
                <div class="form-group">
                    <label>Date Joined</label>
                    <input type="date" id="date_joined" required>
                </div>
                <div class="form-group">
                    <label>Sex</label>
                    <select id="sex" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>NIN</label>
                    <input type="text" id="nin" placeholder="e.g. WK442011B" required>
                </div>
                <div class="form-group">
                    <label>Job Title</label>
                    <select id="job_title" required>
                        <option value="">Select Job Title</option>
                        <option value="Manager">Manager</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Salary</label>
                    <input type="number" step="0.01" id="salary" min="1" placeholder="e.g. 24000.00" required>
                </div>
                <div class="form-group">
                    <label>Branch</label>
                    <select id="branch_no" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->branch_no }}">
                                {{ 'B' . str_pad($branch->branch_no, 3, '0', STR_PAD_LEFT) }} — {{ $branch->city }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Supervisor</label>
                    <select id="supervisor_staff_no">
                        <option value="">No Supervisor / Select branch first</option>
                    </select>
                </div>
            </div>

            {{-- ========================= MANAGER FIELDS ========================= --}}
            <div id="managerFields" style="display:none;">
                <h3>Manager Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Date Start</label>
                        <input type="date" id="date_start">
                    </div>
                    <div class="form-group">
                        <label>Car Allowance</label>
                        <input type="number" step="0.01" min="0" id="car_allowance" placeholder="e.g. 1200.00">
                    </div>
                    <div class="form-group">
                        <label>Bonus</label>
                        <input type="number" step="0.01" min="0" id="bonus" placeholder="e.g. 5000.00">
                    </div>
                </div>
            </div>

            {{-- ========================= SECRETARY FIELDS ========================= --}}
            <div id="secretaryFields" style="display:none;">
                <h3>Secretary Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Typing Speed (WPM)</label>
                        <input type="number" min="1" id="typing_speed" placeholder="e.g. 65">
                    </div>
                </div>
            </div>

            {{-- ========================= NEXT OF KIN ========================= --}}
            <h3>Next of Kin</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="nok_name" placeholder="e.g. Mary Smith" required>
                </div>
                <div class="form-group">
                    <label>Relationship</label>
                    <input type="text" id="nok_relationship" placeholder="e.g. Spouse" required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" id="nok_address" placeholder="e.g. 18 Deer Rd, Glasgow" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" id="nok_phone" placeholder="e.g. 0141-334-5677" required>
                </div>
            </div>

            {{-- BUTTONS --}}
            <div class="form-actions">
                <a href="/staff" class="cancel-btn">Cancel</a>
                <button type="submit" id="submitBtn" class="submit-btn">Create Staff</button>
            </div>

        </form>
    </div>

</div>

<script>
// ---- Role-specific field toggle ----
document.getElementById('job_title').addEventListener('change', function () {
    document.getElementById('managerFields').style.display   = this.value === 'Manager'   ? 'block' : 'none';
    document.getElementById('secretaryFields').style.display = this.value === 'Secretary' ? 'block' : 'none';
});

// ---- Dynamic supervisor dropdown based on selected branch ----
document.getElementById('branch_no').addEventListener('change', async function () {
    const branchNo  = this.value;
    const supSelect = document.getElementById('supervisor_staff_no');
    supSelect.innerHTML = '<option value="">Loading supervisors...</option>';

    if (!branchNo) {
        supSelect.innerHTML = '<option value="">No Supervisor / Select branch first</option>';
        return;
    }

    try {
        const response = await fetch(`/api/staff/supervisors-for-branch?branch_no=${branchNo}`);
        const result   = await response.json();

        supSelect.innerHTML = '<option value="">No Supervisor</option>';

        if (result.success && result.data.length > 0) {
            result.data.forEach(sup => {
                const formatted = 'S' + String(sup.staff_no).padStart(3, '0');
                supSelect.innerHTML += `<option value="${sup.staff_no}">${formatted} — ${sup.first_name} ${sup.last_name}</option>`;
            });
        } else {
            supSelect.innerHTML += '<option value="" disabled>No supervisors in this branch</option>';
        }
    } catch (err) {
        console.error(err);
        supSelect.innerHTML = '<option value="">Error loading supervisors</option>';
    }
});

// ---- Form submit ----
document.getElementById('staffForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled    = true;
    submitBtn.textContent = 'Creating...';

    const payload = {
        first_name:          document.getElementById('first_name').value,
        last_name:           document.getElementById('last_name').value,
        address:             document.getElementById('address').value,
        telephone_no:        document.getElementById('telephone_no').value,
        sex:                 document.getElementById('sex').value,
        date_of_birth:       document.getElementById('date_of_birth').value,
        date_joined:         document.getElementById('date_joined').value,
        nin:                 document.getElementById('nin').value,
        job_title:           document.getElementById('job_title').value,
        salary:              document.getElementById('salary').value,
        branch_no:           document.getElementById('branch_no').value,
        supervisor_staff_no: document.getElementById('supervisor_staff_no').value || null,
        date_start:          document.getElementById('date_start')?.value    || null,
        car_allowance:       document.getElementById('car_allowance')?.value || null,
        bonus:               document.getElementById('bonus')?.value         || null,
        typing_speed:        document.getElementById('typing_speed')?.value  || null,
        nok_name:            document.getElementById('nok_name').value,
        nok_relationship:    document.getElementById('nok_relationship').value,
        nok_address:         document.getElementById('nok_address').value,
        nok_phone:           document.getElementById('nok_phone').value,
    };

    try {
        const res = await fetch('/api/staff', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json();

        if (!res.ok || !data.success) {
            showMessage(data.message || 'Failed to create staff.', 'error');
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Create Staff';
            return;
        }

        showMessage('Staff created successfully!', 'success');
        setTimeout(() => window.location.href = '/staff', 1000);

    } catch (err) {
        console.error(err);
        showMessage('Network or server error. Please try again.', 'error');
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Create Staff';
    }
});

function showMessage(msg, type) {
    const div = document.getElementById('formMessage');
    div.className   = type === 'success' ? 'success-message' : 'error-message';
    div.textContent = msg;
    div.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

@endsection