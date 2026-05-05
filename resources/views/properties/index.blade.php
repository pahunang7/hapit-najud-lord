@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">


<hr>
<h2 class="ptitle">Properties</h2>

<hr>

<div class="table-container">
    <table id="propertiesTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Address</th>
                <th>Type</th>
                <th>Rooms</th>
                <th>Rent</th>
                <th>Status</th>
            </tr>
        </thead>
    <tbody></tbody>
    </table>
</div>

<hr>

<script>
fetch('/api/properties')
.then(res => res.json())
.then(result => {
    let rows = '';

    result.data.forEach(p => {
        rows += `
        <tr>
            <td>${p.property_no}</td>
            <td>${p.full_address}</td>
            <td>${p.property_type}</td>
            <td>${p.no_of_rooms}</td>
            <td>${p.monthly_rent}</td>
            <td class="status ${p.rental_status.toLowerCase()}">
            ${p.rental_status}</td>
        </tr>`;
    });
    document.querySelector('#propertiesTable tbody').innerHTML = rows;
});
</script>
@endsection