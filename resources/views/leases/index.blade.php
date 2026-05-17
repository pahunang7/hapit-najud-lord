@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<hr>

<div class="header-bar">

    <h2 class="ltitle">Leases</h2>

    <div class="lease-controls">

        <input
            type="text"
            id="leaseSearch"
            placeholder="Search lease..."
            onkeyup="loadLeases()"
        >

        <button class="add-btn" onclick="openLeaseModal()">
            + Add Lease
        </button>

    </div>

</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<table id="leasesTable">
    <thead class="lthead">
        <tr>
            <th>Lease No</th>
            <th>Property</th>
            <th>Renter</th>
            <th>Staff</th>
            <th>Start</th>
            <th>End</th>
            <th>Duration</th>
            <th>Deposit</th>
            <th>Deposit Paid</th>
            <th>Payment Method</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody></tbody>
</table>

<hr>

<!-- ================= MODAL ================= -->
<div id="leaseFormModal" class="modal-overlay">

    <div class="modal-box">

        <h3 id="modalTitle">Create Lease</h3>

        <form id="leaseForm">

            <input type="hidden" id="editing_id">

            <input type="number"
                   placeholder="Lease No"
                   id="lease_no"
                   required>

            <input type="date"
                   id="start_date"
                   required>

            <input type="date"
                   id="end_date"
                   required>

            <input type="number"
                   placeholder="Deposit"
                   id="deposit"
                   min="0"
                   step="0.01"
                   required>

            <!-- Deposit Paid -->
            <select id="deposit_paid" required>
                <option value="">Deposit Paid?</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>

            <!-- Payment Method -->
            <select id="payment_method" required>
                <option value="">Select Payment Method</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Cheque">Cheque</option>
            </select>

            <!-- Property -->
            <select id="property_no" required>
                <option value="">Select Property</option>
            </select>

            <!-- Renter -->
            <select id="renter_no" required>
                <option value="">Select Renter</option>
            </select>

            <!-- Staff -->
            <select id="staff_no" required>
                <option value="">Select Staff</option>
            </select>

            <button type="submit">Create</button>

            <button type="button" onclick="closeLeaseModal()">
                Cancel
            </button>

        </form>
    </div>
</div>

<!-- ================= DELETE MODAL ================= -->
<div id="deleteModal" class="modal-overlay">

    <div class="modal-box">

        <p class="modal-text">
            Are you sure you want to delete this lease?
        </p>

        <div class="modal-actions">

            <button class="btn-delete" onclick="confirmDelete()">
                Delete
            </button>

            <button class="btn-cancel" onclick="closeDeleteModal()">
                Cancel
            </button>

        </div>
    </div>
</div>

<script>

let deleteId = null;

// ================= LOAD TABLE =================
function loadLeases() {

    const search = document.getElementById('leaseSearch')?.value || '';

    fetch(`/api/leases?search=${encodeURIComponent(search)}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
    })
    .then(res => res.json())
    .then(data => {

        const tbody = document.querySelector('#leasesTable tbody');
        tbody.innerHTML = '';

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="11">No data found</td></tr>`;
            return;
        }

        data.data.forEach(l => {

            const row = `
            <tr>
                <td>${l.lease_no ?? ''}</td>
                <td>${l.property_no ?? ''} - ${l.property_address ?? ''}</td>
                <td>${l.renter_no   ?? ''} - ${l.renter_name    ?? ''}</td>
                <td>${l.staff_no    ?? ''} - ${l.staff_name     ?? ''}</td>
                <td>${l.start_date  ?? ''}</td>
                <td>${l.end_date    ?? ''}</td>
                <td>${l.duration    ?? ''}</td>
                <td>${l.deposit     ?? ''}</td>
                <td>${l.deposit_paid    ?? ''}</td>
                <td>${l.payment_method  ?? ''}</td>
                <td>
                    <button class="table-btne" onclick="editLease(${l.lease_no})">Edit</button>
                    <button class="table-btnd" onclick="openDeleteModal(${l.lease_no})">Delete</button>
                </td>
            </tr>
            `;

            tbody.innerHTML += row;
        });
    })
    .catch(err => console.error('FETCH ERROR:', err));
}

// ================= LOAD DROPDOWNS =================
// loadFormData() fetches /api/leases/form-data which returns properties,
// renters, and staff all in one call — this is the only dropdown loader needed.
// The old separate loadRenters() function used the wrong field (member.staff_no)
// and has been removed; loadFormData() handles renters correctly.
async function loadFormData() {

    try {
        const res  = await fetch('/api/leases/form-data', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        });

        if (!res.ok) {
            console.error('Failed to load form data:', res.status);
            return;
        }

        const data = await res.json();

        // PROPERTY
        const propertySelect = document.getElementById('property_no');
        propertySelect.innerHTML = `<option value="">Select Property</option>`;

        (data.properties ?? []).forEach(p => {
            propertySelect.innerHTML += `
                <option value="${p.property_no}">
                    #${p.property_no} - ${p.property_type} - ${p.street}, ${p.city}
                </option>
            `;
        });

        // RENTER — use renter_no (not staff_no)
        const renterSelect = document.getElementById('renter_no');
        renterSelect.innerHTML = `<option value="">Select Renter</option>`;

        (data.renters ?? []).forEach(r => {
            renterSelect.innerHTML += `
                <option value="${r.renter_no}">
                    #${r.renter_no} - ${r.renter_name}
                </option>
            `;
        });

        // STAFF — use staff_no
        const staffSelect = document.getElementById('staff_no');
        staffSelect.innerHTML = `<option value="">Select Staff</option>`;

        (data.staff ?? []).forEach(s => {
            staffSelect.innerHTML += `
                <option value="${s.staff_no}">
                    #${s.staff_no} - ${s.staff_name}
                </option>
            `;
        });

    } catch (err) {
        console.error('loadFormData error:', err);
    }
}

// ================= OPEN MODAL =================
function openLeaseModal() {
    document.getElementById('leaseFormModal').style.display = 'flex';
}

// ================= CLOSE MODAL =================
function closeLeaseModal() {
    document.getElementById('leaseFormModal').style.display = 'none';
    document.getElementById('leaseForm').reset();
    document.getElementById('editing_id').value = '';
    document.getElementById('lease_no').disabled = false;
    document.getElementById('modalTitle').textContent = 'Create Lease';
    document.querySelector('#leaseForm button[type="submit"]').textContent = 'Create';
}

// ================= DELETE MODAL =================
function openDeleteModal(id) {
    deleteId = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteId = null;
}

// ================= DELETE =================
function confirmDelete() {

    fetch(`/api/leases/${deleteId}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(res => res.json())
    .then(() => {
        loadLeases();
        showMessage('Lease deleted!');
        closeDeleteModal();
    })
    .catch(() => showMessage('Delete failed', 'error'));
}

// ================= CLICK OUTSIDE =================
window.onclick = function (e) {
    if (e.target === document.getElementById('leaseFormModal')) closeLeaseModal();
    if (e.target === document.getElementById('deleteModal'))    closeDeleteModal();
};

// ================= SUBMIT =================
document.getElementById('leaseForm').addEventListener('submit', function (e) {

    e.preventDefault();

    const id     = document.getElementById('editing_id').value;
    const url    = id ? `/api/leases/${id}` : '/api/leases';
    const method = id ? 'PUT' : 'POST';

    const data = {
        start_date:     document.getElementById('start_date').value,
        end_date:       document.getElementById('end_date').value,
        deposit:        document.getElementById('deposit').value,
        deposit_paid:   document.getElementById('deposit_paid').value,
        payment_method: document.getElementById('payment_method').value,
        property_no:    document.getElementById('property_no').value,
        renter_no:      document.getElementById('renter_no').value,
        staff_no:       document.getElementById('staff_no').value,
    };

    if (!id) {
        data.lease_no = document.getElementById('lease_no').value;
    }

    if (parseFloat(data.deposit) <= 0) {
        showMessage('Deposit cannot be negative', 'error');
        return;
    }

    fetch(url, {
        method:      method,
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(data),
    })
    .then(async response => {

        const res = await response.json();

        if (res.errors) {
            const firstError = Object.values(res.errors)[0][0];
            showMessage(firstError, 'error');
            return;
        }

        if (res.status === 'error') {
            showMessage(res.message || 'Something went wrong', 'error');
            return;
        }

        if (res.status === 'info') {
            showMessage(res.message, 'info');
            return;
        }

        closeLeaseModal();
        loadLeases();
        showMessage(res.message || (id ? 'Lease updated!' : 'Lease created!'), 'success');
    })
    .catch(err => {
        console.error(err);
        showMessage('Something went wrong', 'error');
    });
});

// ================= EDIT =================
function editLease(id) {

    fetch(`/api/leases/${id}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
    })
    .then(res => res.json())
    .then(res => {

        const l = res.data;

        openLeaseModal();

        document.getElementById('editing_id').value    = id;
        document.getElementById('lease_no').value      = l.lease_no;
        document.getElementById('start_date').value    = l.start_date;
        document.getElementById('end_date').value      = l.end_date;
        document.getElementById('deposit').value       = l.deposit;
        document.getElementById('deposit_paid').value  = l.deposit_paid;
        document.getElementById('payment_method').value = l.payment_method;
        document.getElementById('property_no').value   = l.property_no;
        document.getElementById('renter_no').value     = l.renter_no;
        document.getElementById('staff_no').value      = l.staff_no;

        document.getElementById('lease_no').disabled = true;
        document.getElementById('modalTitle').textContent = 'Edit Lease';
        document.querySelector('#leaseForm button[type="submit"]').textContent = 'Update';
    });
}

// ================= TOAST =================
function showMessage(message, type = 'success') {

    const box = document.createElement('div');
    box.innerText = message;

    box.style.cssText = `
        position: fixed; top: 20px; right: 20px;
        padding: 14px 22px; color: #fff;
        border-radius: 8px; z-index: 9999;
        font-family: 'Inter', sans-serif;
        font-size: 14px; font-weight: 600;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        background: ${type === 'error' ? '#e74c3c' : type === 'info' ? '#f39c12' : '#27ae60'};
    `;

    document.body.appendChild(box);

    setTimeout(() => {
        box.style.opacity    = '0';
        box.style.transition = '0.3s';
        setTimeout(() => box.remove(), 300);
    }, 2000);
}

// ================= INIT =================
loadLeases();
loadFormData();

</script>

@endsection