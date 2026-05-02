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
        </tr>
    </thead>
    <tbody></tbody>
</table>



<form id="leaseForm">
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
    <button type="submit">Create</button>
</form>

<script>
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
            </tr>`;
        });
        document.querySelector('#leasesTable tbody').innerHTML = rows;
    });
}

loadLeases();

// SUBMIT FORM
document.getElementById('leaseForm').addEventListener('submit', function(e) {
    e.preventDefault();

    document.getElementById('leaseForm').addEventListener('submit', function(e) {
    e.preventDefault(); // ✅ keep this

    fetch('/api/leases', {   // 🔁 replace ONLY this block
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            lease_no: document.getElementById('lease_no').value,
            start_date: document.getElementById('start_date').value,
            end_date: document.getElementById('end_date').value,
            duration: document.getElementById('duration').value,
            deposit: document.getElementById('deposit').value,
            deposit_paid: document.getElementById('deposit_paid').value,
            payment_method: document.getElementById('payment_method').value,
            property_no: document.getElementById('property_no').value,
            renter_no: document.getElementById('renter_no').value,
            staff_no: document.getElementById('staff_no').value
        })
    })
    .then(async res => {
        if (!res.ok) {
            let error = await res.text();
            throw new Error(error);
        }
        return res.json();
    })
    .then(() => {
        alert('✅ Lease created!');
        loadLeases();
    })
    .catch(err => {
        console.error(err);
        alert('❌ ERROR:\n' + err.message);
    });
});
});
</script>
@endsection