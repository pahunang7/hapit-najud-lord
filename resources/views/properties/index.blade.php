@extends('layouts.app')

@section('content')
<h2 class="ptitle">Properties</h2>

<table id="propertiesTable">
    <thead class="phead">
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
            <td>${p.rental_status}</td>
        </tr>`;
    });
    document.querySelector('#propertiesTable tbody').innerHTML = rows;
});
</script>
@endsection