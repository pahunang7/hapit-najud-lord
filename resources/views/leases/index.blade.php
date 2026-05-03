@extends('layouts.app')

@section('content')

<hr>
<h2 class="ltitle">Leases</h2>

<meta name="csrf-token" content="{{ csrf_token() }}">

<table id="leasesTable">
    <thead class="lthead">
        <tr>
            <th>Lease No</th>
            <th>Property</th>
            <th>Renter</th>
            <th>Start</th>
            <th>End</th>
            <th>Duration</th>
            <th>Deposit</th>
            <th>Deposit Paid</th>
            <th>Payment Method</th>
            <th>Staff No</th>
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

    <!-- FIXED -->
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

<script>
let originalLease = null;

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
                <td>${l.property_no}</td>
                <td>${l.renter_no}</td>
                <td>${l.start_date}</td>
                <td>${l.end_date}</td>
                <td>${l.duration}</td>
                <td>${l.deposit}</td>
                <td>${l.deposit_paid}</td>
                <td>${l.payment_method}</td>
                <td>${l.staff_no}</td>
                <td>
                    <button class="table-btne" onclick="editLease(${l.lease_no})">Edit</button>
                    <button class="table-btnd" onclick="deleteLease(${l.lease_no})">Delete</button>
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

    // BASIC VALIDATION
    if (!id && (!data.lease_no || !data.start_date || !data.end_date || !data.deposit)) {
        alert("Please fill all required fields");
        return;
    }

    // PREVENT EMPTY UPDATE
    if (id && originalLease) {
        let changed =
            data.start_date !== originalLease.start_date ||
            data.end_date !== originalLease.end_date ||
            data.deposit != originalLease.deposit ||
            data.deposit_paid !== originalLease.deposit_paid ||
            data.payment_method !== originalLease.payment_method ||
            data.property_no != originalLease.property_no ||
            data.renter_no != originalLease.renter_no ||
            data.staff_no != originalLease.staff_no;

        if (!changed) {
            alert("No changes detected!");
            return;
        }
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
        // ❌ HANDLE ERROR PROPERLY
        if (responseData.error) {
            alert(responseData.error);
        } else if (responseData.errors) {
            alert(Object.values(responseData.errors).join('\n'));
        } else {
            alert("Something went wrong");
        }
        throw new Error("Request failed");
    }

    return responseData;
})
.then(() => {
    alert(id ? 'Lease updated!' : 'Lease created!');

    cancelEdit();
    loadLeases();
})
.catch(err => {
    console.error(err);
});
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
function deleteLease(id) {
    if (!confirm("Are you sure you want to delete this lease?")) return;

    fetch(`/api/leases/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(() => {
        alert('Lease deleted!');
        loadLeases();
    })
    .catch(err => {
        console.error(err);
        alert('Delete failed');
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

// ================= INITIAL LOAD =================
loadLeases();

</script>

@endsection