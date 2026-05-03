@extends('layouts.app')

@section('content')
<hr>
<h2 class="vtitle">Viewings</h2>

<meta name="csrf-token" content="{{ csrf_token() }}">

<table id="viewingsTable">
    <thead class="vhead">
        <tr>
            <th>Property</th>
            <th>Renter</th>
            <th>Date</th>
            <th>Comments</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<hr>


<form id="viewingForm">
    <h3 class="createv">Record Viewing</h3>
    <input type="number" placeholder="Property No" id="property_no" required>
    <input type="number" placeholder="Renter No" id="renter_no" required>
    <input type="date" id="viewing_date" required>
    <input type="text" placeholder="Comments" id="comments">
    <button type="submit">Submit</button>
    <button type="button" id="cancelBtn" onclick="cancelViewing()" style="display:none;">
    Cancel
</button>
</form>

<script>
// LOAD VIEWINGS
function loadViewings() {
    fetch('/api/viewings')
    .then(res => res.json())
    .then(res => {
        let rows = '';
        res.data.forEach(v => {
            rows += `
            <tr>
                <td>${v.property_no} - ${v.property_address} </td>
                <td>${v.renter_no} - ${v.renter_name}</td>
                <td>${v.viewing_date}</td>
                <td>${v.comments}</td>
            </tr>`;
        });
        document.querySelector('#viewingsTable tbody').innerHTML = rows;
    });
}

loadViewings();

const inputs = document.querySelectorAll('#viewingForm input');
inputs.forEach(input => {
    input.addEventListener('input', () => {
        let hasValue = Array.from(inputs).some(i => i.value !== '');
        document.getElementById('cancelBtn').style.display = hasValue ? 'inline-block' : 'none';
    });
});

function cancelViewing() {
    document.getElementById('viewingForm').reset();
    document.getElementById('cancelBtn').style.display = 'none';
}

// SUBMIT FORM
document.getElementById('viewingForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const data = {
        property_no: document.getElementById('property_no').value,
        renter_no: document.getElementById('renter_no').value,
        viewing_date: document.getElementById('viewing_date').value,
        comments: document.getElementById('comments').value
    };

    try {
        const res = await fetch('/api/viewings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        const response = await res.json(); // ✅ ALWAYS parse JSON

        if (!res.ok) {
            showMessage(response.message || "Something went wrong", "error"); // ✅ clean
            return;
        }

        showMessage(response.message, "success"); // ✅ clean success

        loadViewings();
        document.getElementById('viewingForm').reset();
        document.getElementById('cancelBtn').style.display = 'none';

    } catch (err) {
        console.error(err);
        showMessage("Unexpected error occurred", "error");
    }
});

</script>

<script>
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
    box.style.transition = "0.3s";

    box.style.backgroundColor = (type === "error") ? "#e74c3c" : "#2ecc71";

    document.body.appendChild(box);

    setTimeout(() => box.remove(), 3000);
}
</script>
@endsection