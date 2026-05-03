@extends('layouts.app')

@section('content')
<hr>
<h2 class="vtitle">Viewings</h2>
<hr>

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




<form id="viewingForm">
    <h3 class="createv">Record Viewing</h3>
    <input type="number" placeholder="Property No" id="property_no" required>
    <input type="number" placeholder="Renter No" id="renter_no" required>
    <input type="date" id="viewing_date" required>
    <input type="text" placeholder="Comments" id="comments">
    <button type="submit">Submit</button>
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
                <td>${v.property_no}</td>
                <td>${v.renter_no}</td>
                <td>${v.viewing_date}</td>
                <td>${v.comments}</td>
            </tr>`;
        });
        document.querySelector('#viewingsTable tbody').innerHTML = rows;
    });
}

loadViewings();

// SUBMIT FORM
document.getElementById('viewingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    fetch('/api/viewings', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            property_no: document.getElementById('property_no').value,
            renter_no: document.getElementById('renter_no').value,
            viewing_date: document.getElementById('viewing_date').value,
            comments: document.getElementById('comments').value
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
        alert('Viewing recorded!');
        loadViewings();
    })
    .catch(err => {
        console.error(err);
        alert('ERROR:\n' + err.message);
    });
});
</script>
@endsection