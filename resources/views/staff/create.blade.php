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

            <h3>Basic Information</h3>

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
                    <label>Telephone No.</label>
                    <input type="text" id="telephone_no" required>
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
                    <input type="text" id="nin" required>
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
                    <input type="number" step="0.01" id="salary" required>
                </div>

                <div class="form-group">
                    <label>Branch</label>
                    <select id="branch_no" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->branch_no }}">
                                {{ 'B' . str_pad($branch->branch_no, 3, '0', STR_PAD_LEFT) }}
                                — {{ $branch->city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Supervisor</label>
                    <select id="supervisor_staff_no">
                        <option value="">No Supervisor</option>
                    </select>
                </div>

            </div>

            <div id="managerFields" style="display:none;">
                <h3>Manager Details</h3>
                <div class="form-grid">
                    <input type="date" id="date_start">
                    <input type="number" id="car_allowance" placeholder="Car Allowance">
                    <input type="number" id="bonus" placeholder="Bonus">
                </div>
            </div>

            <div id="secretaryFields" style="display:none;">
                <h3>Secretary Details</h3>
                <div class="form-grid">
                    <input type="number" id="typing_speed" placeholder="Typing Speed">
                </div>
            </div>

            <h3>Next of Kin</h3>

            <div class="form-grid">
                <input type="text" id="nok_name" placeholder="Full Name" required>
                <input type="text" id="nok_relationship" placeholder="Relationship" required>
                <input type="text" id="nok_address" placeholder="Address" required>
                <input type="text" id="nok_phone" placeholder="Phone" required>
            </div>

            <div class="form-actions">
                <a href="/staff" class="cancel-btn">Cancel</a>
                <button type="submit" id="submitBtn" class="submit-btn">
                    Create Staff
                </button>
            </div>

        </form>
    </div>

</div>

<script>

// Toggle job-specific fields
document.getElementById('job_title').addEventListener('change', function () {
    document.getElementById('managerFields').style.display =
        this.value === 'Manager' ? 'block' : 'none';

    document.getElementById('secretaryFields').style.display =
        this.value === 'Secretary' ? 'block' : 'none';
});

// Load supervisors by branch
document.getElementById('branch_no').addEventListener('change', async function () {

    const branchNo = this.value;
    const supSelect = document.getElementById('supervisor_staff_no');

    if (!branchNo) {
        supSelect.innerHTML = '<option value="">No Supervisor</option>';
        return;
    }

    supSelect.innerHTML = '<option>Loading...</option>';

    try {
        const response = await fetch(
            `/api/staff/supervisors-for-branch?branch_no=${branchNo}`,
            {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        const result = await response.json();

        supSelect.innerHTML = '<option value="">No Supervisor</option>';

        const data = result.data || [];

        data.forEach(s => {
            supSelect.innerHTML += `
                <option value="${s.staff_no}">
                    ${s.first_name} ${s.last_name}
                </option>
            `;
        });

    } catch (err) {
        console.error(err);
        supSelect.innerHTML = '<option>Error loading</option>';
    }
});

// CREATE STAFF (FIXED: /staff NOT /api/staff)
document.getElementById('staffForm').addEventListener('submit', async function (e) {

    e.preventDefault();

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Creating...';

    const payload = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        address: document.getElementById('address').value,
        telephone_no: document.getElementById('telephone_no').value,
        sex: document.getElementById('sex').value,
        date_of_birth: document.getElementById('date_of_birth').value,
        date_joined: document.getElementById('date_joined').value,
        nin: document.getElementById('nin').value,
        job_title: document.getElementById('job_title').value,
        salary: document.getElementById('salary').value,
        branch_no: document.getElementById('branch_no').value,
        supervisor_staff_no: document.getElementById('supervisor_staff_no').value || null,
        date_start: document.getElementById('date_start')?.value || null,
        car_allowance: document.getElementById('car_allowance')?.value || null,
        bonus: document.getElementById('bonus')?.value || null,
        typing_speed: document.getElementById('typing_speed')?.value || null,
        nok_name: document.getElementById('nok_name').value,
        nok_relationship: document.getElementById('nok_relationship').value,
        nok_address: document.getElementById('nok_address').value,
        nok_phone: document.getElementById('nok_phone').value,
    };

    try {

        const response = await fetch('/staff', {   // ✅ FIXED HERE

            method: 'POST',
            credentials: 'same-origin',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },

            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            showMessage(data.message || 'Failed to create staff', 'error');
            btn.disabled = false;
            btn.textContent = 'Create Staff';
            return;
        }

        showMessage('Staff created successfully!', 'success');

        setTimeout(() => {
            window.location.href = '/staff';
        }, 800);

    } catch (err) {
        console.error(err);
        showMessage('Server error occurred.', 'error');

        btn.disabled = false;
        btn.textContent = 'Create Staff';
    }
});

function showMessage(msg, type) {
    const div = document.getElementById('formMessage');
    div.className = type === 'success' ? 'success-message' : 'error-message';
    div.textContent = msg;
}

</script>

@endsection