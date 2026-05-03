@extends('layouts.app')

@section('content')

<hr>
<h2 class="ltitle">Leases</h2>
<hr>

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



<form id="leaseForm">
    <input type="hidden" id="editing_id">
    <h3 class="createl">Create Lease</h3>
    <input type="number" placeholder="Lease No" id="lease_no" required>
    <input type="date" id="start_date" required>
    <input type="date" id="end_date" required>
    <input type="number" placeholder="Duration" id="duration" required>
    <input type="number" placeholder="Deposit" id="deposit" required>
    <input type="text" placeholder="Deposit Paid (Yes/No)" id="deposit_paid">
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
// LOAD LEASES
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
                    <button class="table-btn" onclick="editLease(${l.lease_no})">Edit</button>
                    <button class="table-btn" onclick="deleteLease(${l.lease_no})">Delete</button>
                </td>
            </tr>`;
        });
        document.querySelector('#leasesTable tbody').innerHTML = rows;
    });
}

loadLeases();

// SUBMIT FORM
document.getElementById('leaseForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let id = document.getElementById('editing_id').value;

    let url = '/api/leases';
    let method = 'POST';

    if (id) {
        url = `/api/leases/${id}`;
        method = 'PUT';
    }

    // ✅ FIX: separate data object
    let data = {
        start_date: document.getElementById('start_date').value,
        end_date: document.getElementById('end_date').value,
        duration: document.getElementById('duration').value,
        deposit: document.getElementById('deposit').value,
        deposit_paid: document.getElementById('deposit_paid').value,
        payment_method: document.getElementById('payment_method').value,
        property_no: document.getElementById('property_no').value,
        renter_no: document.getElementById('renter_no').value,
        staff_no: document.getElementById('staff_no').value
    };

    // ✅ only include lease_no if creating
    if (!id) {
        data.lease_no = document.getElementById('lease_no').value;
    }

    // ✅ FIX OPTION 1: require fields when creating
if (!id) {
    if (
        !document.getElementById('lease_no').value ||
        !data.start_date ||
        !data.end_date ||
        !data.duration ||
        !data.deposit
    ) {
        alert("Please fill all required fields");
        return;
    }
}

// ✅ FIX OPTION 2: prevent empty update
if (id && originalLease) {
    let changed = (
        data.start_date !== originalLease.start_date ||
        data.end_date !== originalLease.end_date ||
        data.duration != originalLease.duration ||
        data.deposit != originalLease.deposit ||
        data.deposit_paid !== originalLease.deposit_paid ||
        data.payment_method !== originalLease.payment_method ||
        data.property_no != originalLease.property_no ||
        data.renter_no != originalLease.renter_no ||
        data.staff_no != originalLease.staff_no
    );

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
    .then(res => res.json())
    .then(() => {
        alert(id ? 'Lease updated!' : 'Lease created!');

        document.getElementById('leaseForm').reset();
        document.getElementById('editing_id').value = '';

        // ✅ re-enable lease_no after editing
        document.getElementById('lease_no').disabled = false;

        document.querySelector('#leaseForm button[type="submit"]').textContent = "Update Lease";

        loadLeases();
    })
    .catch(err => {
        console.error(err);
        alert('Error occurred');
    });
});

function cancelEdit() {
    document.getElementById('leaseForm').reset();
    document.getElementById('editing_id').value = '';

    // re-enable lease_no
    document.getElementById('lease_no').disabled = false;

    // reset button text
    document.querySelector('#leaseForm button[type="submit"]').textContent = "Create";

    // reset title
    document.querySelector('.createl').textContent = "Create Lease";

    // hide cancel button
    document.getElementById('cancelBtn').style.display = 'none';
}

function editLease(id) {
    fetch(`/api/leases/${id}`)
    .then(res => res.json())
    .then(res => {
        let l = res.data;

        originalLease = { ...l }; // ✅ store original

        document.getElementById('editing_id').value = id;

        document.getElementById('lease_no').value = l.lease_no;
        document.getElementById('start_date').value = l.start_date;
        document.getElementById('end_date').value = l.end_date;
        document.getElementById('duration').value = l.duration;
        document.getElementById('deposit').value = l.deposit;
        document.getElementById('deposit_paid').value = l.deposit_paid;
        document.getElementById('payment_method').value = l.payment_method;
        document.getElementById('property_no').value = l.property_no;
        document.getElementById('renter_no').value = l.renter_no;
        document.getElementById('staff_no').value = l.staff_no;
        document.getElementById('cancelBtn').style.display = 'inline-block';

        // ✅ FIX: only disable (no undo)
        document.getElementById('lease_no').disabled = true;

        document.querySelector('#leaseForm button').textContent = "Update Lease";
    });
}

function deleteLease(id) {
    if (!confirm("Are you sure you want to delete this lease?")) return;

    fetch(`/api/leases/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(() => {
        alert('Lease deleted!');
        loadLeases();
    })
    .catch(err => {
        console.error(err);
        alert('Delete failed');
    });
}

</script>
@endsection