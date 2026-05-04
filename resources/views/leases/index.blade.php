@extends('layouts.app')

@section('content')

<style>
/* ===== DELETE MODAL ===== */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;

    background: rgba(0,0,0,0.5);

    align-items: center;
    justify-content: center;

    z-index: 9999;
}

.modal-box {
    background: #fff;
    padding: 25px;
    width: 320px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal-text {
    margin-bottom: 20px;
}

.modal-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.btn-delete {
    background: #e74c3c;
    color: #fff;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-cancel {
    background: #34495e;
    color: #fff;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-delete:hover { background: #c0392b; }
.btn-cancel:hover { background: #2c3e50; }
</style>

<hr>
<h2 class="ltitle">Leases</h2>

<meta name="csrf-token" content="{{ csrf_token() }}">

<table id="leasesTable">
    <thead class="lthead">
        <tr>
            <th>Lease No</th>
            <th>Property</th>
            <th>Renter</th>
            <th>Staff No</th>
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

<form id="leaseForm">
    <input type="hidden" id="editing_id">

    <h3 class="createl">Create Lease</h3>

    <input type="number" placeholder="Lease No" id="lease_no" required>
    <input type="date" id="start_date" required>
    <input type="date" id="end_date" required>
    <input type="number" placeholder="Deposit" id="deposit" required>

    <select id="deposit_paid">
        <option value="">Deposit Paid?</option>
        <option value="Yes">Yes</option>
        <option value="No">No</option>
    </select>

    <input type="text" placeholder="Payment Method" id="payment_method">
    <input type="number" placeholder="Property No" id="property_no">
    <input type="number" placeholder="Renter No" id="renter_no">
    <input type="number" placeholder="Staff No" id="staff_no">

    <button type="submit" class="form-btn">Create</button>

    <button type="button" class="form-btn" onclick="cancelEdit()" id="cancelBtn" style="display:none;">
        Cancel
    </button>
</form>

<!-- ✅ DELETE MODAL (CORRECT POSITION) -->
<div id="deleteModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <p class="modal-text">Are you sure you want to delete this lease?</p>

        <div class="modal-actions">
            <button class="btn-delete" onclick="confirmDelete()">Delete</button>
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
let originalLease = null;
let deleteId = null;

// ================= LOAD TABLE =================
function loadLeases() {
    fetch('/api/leases')
    .then(res => res.json())
    .then(data => {
        let rows = '';

        data.data.forEach(l => {
            rows += `
<tr>
    <td>${l.lease_no}</td>

    <td>${l.property_no} - ${l.property_address}</td>

    <td>${l.renter_no} - ${l.renter_name}</td>

    <td>${l.staff_no} - ${l.staff_name}</td>

    <td>${l.start_date}</td>
    <td>${l.end_date}</td>
    <td>${l.duration}</td>
    <td>${l.deposit}</td>
    <td>${l.deposit_paid}</td>
    <td>${l.payment_method}</td>

    <td>
        <button class="table-btne" onclick="editLease(${l.lease_no})">Edit</button>
        <button class="table-btnd" onclick="openDeleteModal(${l.lease_no})">Delete</button>
    </td>
</tr>`;
        });

        document.querySelector('#leasesTable tbody').innerHTML = rows;
    });
}

// ================= FORM SUBMIT =================
document.getElementById('leaseForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let id = document.getElementById('editing_id').value;

    let url = '/api/leases';
    let method = 'POST';

    if (id) {
        url = `/api/leases/${id}`;
        method = 'PUT';
    }

    let data = {
        start_date: document.getElementById('start_date').value,
        end_date: document.getElementById('end_date').value,
        deposit: document.getElementById('deposit').value,
        deposit_paid: document.getElementById('deposit_paid').value,
        payment_method: document.getElementById('payment_method').value,
        property_no: document.getElementById('property_no').value,
        renter_no: document.getElementById('renter_no').value,
        staff_no: document.getElementById('staff_no').value
    };

    if (!id) {
        data.lease_no = document.getElementById('lease_no').value;
    }

    if (!id && (!data.lease_no || !data.start_date || !data.end_date || !data.deposit)) {
        showMessage("Please fill all required fields", "error");
        return;
    }

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(async res => {
        let responseData = await res.json();

        if (!res.ok) {
            showMessage(responseData.message || "Something went wrong", "error");
            throw new Error();
        }

        return responseData;
    })
    .then(() => {
        showMessage(id ? 'Lease updated successfully!' : 'Lease created successfully!', 'success');
        cancelEdit();
        loadLeases();
    })
    .catch(() => {});
});

// ================= EDIT =================
function editLease(id) {
    fetch(`/api/leases/${id}`)
    .then(res => res.json())
    .then(res => {
        let l = res.data;

        originalLease = { ...l };

        document.getElementById('editing_id').value = id;

        document.getElementById('lease_no').value = l.lease_no;
        document.getElementById('start_date').value = l.start_date;
        document.getElementById('end_date').value = l.end_date;
        document.getElementById('deposit').value = l.deposit;
        document.getElementById('deposit_paid').value = l.deposit_paid;
        document.getElementById('payment_method').value = l.payment_method;
        document.getElementById('property_no').value = l.property_no;
        document.getElementById('renter_no').value = l.renter_no;
        document.getElementById('staff_no').value = l.staff_no;

        document.getElementById('lease_no').disabled = true;

        document.querySelector('#leaseForm button[type="submit"]').textContent = "Update Lease";
        document.querySelector('.createl').textContent = "Edit Lease";

        document.getElementById('cancelBtn').style.display = 'inline-block';
    });
}

// ================= DELETE =================
function openDeleteModal(id) {
    deleteId = id;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteId = null;
}

function confirmDelete() {
    fetch(`/api/leases/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(() => {
        showMessage('Lease deleted successfully!', 'success');
        loadLeases();
        closeModal();
    })
    .catch(() => {
        showMessage('Delete failed', 'error');
    });
}

// ================= CANCEL =================
function cancelEdit() {
    document.getElementById('leaseForm').reset();
    document.getElementById('editing_id').value = '';
    document.getElementById('lease_no').disabled = false;

    document.querySelector('#leaseForm button[type="submit"]').textContent = "Create";
    document.querySelector('.createl').textContent = "Create Lease";

    document.getElementById('cancelBtn').style.display = 'none';

    originalLease = null;
}

// ================= TOAST =================
function showMessage(message, type = "success") {
    const box = document.createElement("div");

    box.innerText = message;

    box.style.position = "fixed";
    box.style.top = "20px";
    box.style.right = "20px";
    box.style.padding = "12px 20px";
    box.style.color = "#fff";
    box.style.borderRadius = "6px";
    box.style.zIndex = "9999";
    box.style.fontWeight = "bold";
    box.style.boxShadow = "0 4px 10px rgba(0,0,0,0.2)";

    box.style.backgroundColor = (type === "error") ? "#e74c3c" : "#2ecc71";

    document.body.appendChild(box);

    setTimeout(() => box.remove(), 3000);
}

// ================= INITIAL LOAD =================
loadLeases();

</script>

@endsection