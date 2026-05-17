@extends('layouts.app')
@section('content')

<div class="page-header">
    <div>
        <h1>Edit Staff</h1>
        <p>Update staff information</p>
    </div>
</div>

<div class="form-card">
<form id="editStaffForm">

    {{-- BASIC INFO --}}
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
            <label>Telephone No</label>
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
            <input type="number" step="0.01" min="1" id="salary" required>
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
                @foreach($supervisors as $sup)
                    <option value="{{ $sup->staff_no }}">
                        {{ 'S' . str_pad($sup->staff_no, 3, '0', STR_PAD_LEFT) }}
                        — {{ $sup->first_name }} {{ $sup->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    {{-- MANAGER --}}
    <div id="managerFields" style="display:none;">
        <h3>Manager Details</h3>
        <div class="form-grid">
            <input type="date" id="date_start">
            <input type="number" id="car_allowance" placeholder="Car Allowance">
            <input type="number" id="bonus" placeholder="Bonus">
        </div>
    </div>

    {{-- SECRETARY --}}
    <div id="secretaryFields" style="display:none;">
        <h3>Secretary Details</h3>
        <div class="form-grid">
            <input type="number" id="typing_speed" placeholder="Typing Speed">
        </div>
    </div>

    {{-- NEXT OF KIN --}}
    <h3>Next of Kin</h3>
    <div class="form-grid">
        <input type="text" id="nok_name" placeholder="Full Name">
        <input type="text" id="nok_relationship" placeholder="Relationship">
        <input type="text" id="nok_address" placeholder="Address">
        <input type="text" id="nok_phone" placeholder="Phone">
    </div>

    <div class="form-actions">
        <button type="submit">Update Staff</button>
        <a href="/staff" class="cancel-btn">Cancel</a>
    </div>

</form>
</div>

<script>

const staffId = window.location.pathname.split('/')[2];

/* ================= ROLE TOGGLE ================= */
document.getElementById('job_title').addEventListener('change', function () {
    document.getElementById('managerFields').style.display =
        this.value === 'Manager' ? 'block' : 'none';

    document.getElementById('secretaryFields').style.display =
        this.value === 'Secretary' ? 'block' : 'none';
});

/* ================= SUPERVISOR LOAD ================= */
document.getElementById('branch_no').addEventListener('change', async function () {
    await refreshSupervisors(this.value, null);
});

async function refreshSupervisors(branchNo, selectedSupervisor = null) {
    const supSelect = document.getElementById('supervisor_staff_no');

    if (!branchNo) {
        supSelect.innerHTML = '<option value="">No Supervisor</option>';
        return;
    }

    try {
        const res = await fetch(`/api/staff/supervisors-for-branch?branch_no=${branchNo}`, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await res.json();

        supSelect.innerHTML = '<option value="">No Supervisor</option>';

        const data = result.data ?? [];

        data.forEach(s => {
            const selected = selectedSupervisor == s.staff_no ? 'selected' : '';
            supSelect.innerHTML += `
                <option value="${s.staff_no}" ${selected}>
                    ${s.first_name} ${s.last_name}
                </option>
            `;
        });

    } catch (err) {
        console.error(err);
    }
}

/* ================= LOAD STAFF ================= */
async function loadStaff() {
    try {
        const res = await fetch(`/api/staff/${staffId}`, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await res.json();

        if (!result.success) {
            alert('Failed to load staff');
            return;
        }

        const s = result.data;

        document.getElementById('first_name').value   = s.first_name ?? '';
        document.getElementById('last_name').value    = s.last_name ?? '';
        document.getElementById('address').value      = s.address ?? '';
        document.getElementById('telephone_no').value = s.telephone_no ?? '';
        document.getElementById('date_of_birth').value = s.date_of_birth ?? '';
        document.getElementById('date_joined').value  = s.date_joined ?? '';
        document.getElementById('sex').value          = s.sex ?? '';
        document.getElementById('nin').value          = s.nin ?? '';
        document.getElementById('job_title').value    = s.job_title ?? '';
        document.getElementById('salary').value       = s.salary ?? '';
        document.getElementById('branch_no').value    = s.branch_no ?? '';

        await refreshSupervisors(s.branch_no, s.supervisor_staff_no);

        if (s.job_title === 'Manager') {
            document.getElementById('managerFields').style.display = 'block';
            document.getElementById('date_start').value    = s.date_start ?? '';
            document.getElementById('car_allowance').value = s.car_allowance ?? '';
            document.getElementById('bonus').value         = s.bonus ?? '';
        }

        if (s.job_title === 'Secretary') {
            document.getElementById('secretaryFields').style.display = 'block';
            document.getElementById('typing_speed').value = s.typing_speed ?? '';
        }

        const nok = s.next_of_kin ?? null;

        document.getElementById('nok_name').value =
            nok?.full_name ?? '';

        document.getElementById('nok_relationship').value =
            nok?.relationship ?? '';

        document.getElementById('nok_address').value =
            nok?.address ?? '';

        document.getElementById('nok_phone').value =
            nok?.telephone_no ?? '';
        
        console.log(s.next_of_kin);

    } catch (err) {
        console.error(err);
        alert('Error loading staff data');
    }
}

/* ================= UPDATE STAFF ================= */
document.getElementById('editStaffForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const payload = {
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        address: document.getElementById('address').value,
        telephone_no: document.getElementById('telephone_no').value,
        date_of_birth: document.getElementById('date_of_birth').value,
        date_joined: document.getElementById('date_joined').value,
        sex: document.getElementById('sex').value,
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

    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Updating...';

    try {
        const res = await fetch(`/staff/${staffId}`, {
            method: 'PUT',
            credentials: 'same-origin', // 🔥 THIS IS REQUIRED
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload),
        });

        const result = await res.json();

        if (!result.success) {
            alert(result.message || 'Update failed');
            btn.disabled = false;
            btn.textContent = 'Update Staff';
            return;
        }

        alert('Staff updated successfully');
        window.location.href = '/staff';

    } catch (err) {
        console.error(err);
        alert('Something went wrong');
        btn.disabled = false;
        btn.textContent = 'Update Staff';
    }
});

/* INIT */
loadStaff();

</script>

@endsection