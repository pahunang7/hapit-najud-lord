@extends('layouts.app')

@section('content')

<div class="page-header">

    <div>
        <h1>Search Properties</h1>
        <p>Find properties based on client preferences.</p>
    </div>

</div>

<div class="search-property-card">

    <!-- SELECT CLIENT -->
    <div class="form-group full-width">

        <label>Select Client</label>

        <select id="renterSelect">

            <option value="">
                Select Client
            </option>

        </select>

    </div>

    <!-- SEARCH BUTTON -->
    <div class="button-row-left">

        <button
            class="primary-btn small-btn"
            onclick="searchProperties()">

            Search

        </button>

    </div>

    <!-- CLIENT PREFERENCES -->
    <h3 class="section-title">
        Client Preferences
    </h3>

    <div class="property-search-grid">

        <div class="search-form-group">

            <label>Preferred Type</label>

            <input
                type="text"
                id="preferred_type"
                readonly
            >

        </div>

        <div class="search-form-group">

            <label>Preferred Location</label>

            <input
                type="text"
                id="preferred_location"
                readonly
            >

        </div>

        <div class="search-form-group">

            <label>Max Rent</label>

            <input
                type="number"
                id="max_rent"
                readonly
            >

        </div>

    </div>

    <!-- RESULTS TABLE -->
    <table class="search-property-table">

        <thead>

            <tr>

                <th>Property No</th>
                <th>Street</th>
                <th>Area</th>
                <th>City</th>
                <th>Property Type</th>
                <th>Rooms</th>
                <th>Monthly Rent</th>

            </tr>

        </thead>

        <tbody id="propertyResults">

        </tbody>

    </table>

</div>

<script>

async function loadRenters() {

    try {

        const response =
            await fetch('/api/renters');

        const result =
            await response.json();

        const renters = result.data;

        const renterSelect =
            document.getElementById('renterSelect');

        renters.forEach(renter => {

            renterSelect.innerHTML += `
                <option value="${renter.renter_no}">

                    ${renter.renter_no}
                    -
                    ${renter.first_name}
                    ${renter.last_name}

                </option>
            `;

        });

    } catch (error) {

        console.error(error);

    }

}

async function searchProperties() {

    const renterId =
        document.getElementById('renterSelect').value;

    if (!renterId) {

        alert('Please select a client.');

        return;
    }

    try {

        const response =
            await fetch(
                `/api/properties/search?renter_no=${renterId}`
            );

        const result =
            await response.json();

        const renter =
            result.renter;

        const properties =
            result.properties;

        // PREFILL PREFERENCES
        document.getElementById('preferred_type').value =
            renter.preferred_type ?? '';

        document.getElementById('preferred_location').value =
            renter.preferred_location ?? '';

        document.getElementById('max_rent').value =
            renter.max_rent ?? '';

        // TABLE
        const tableBody =
            document.getElementById('propertyResults');

        tableBody.innerHTML = '';

        if (properties.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="7">
                        No matching properties found.
                    </td>
                </tr>
            `;

            return;
        }

        properties.forEach(property => {

            tableBody.innerHTML += `

                <tr>

                    <td>${property.property_no}</td>

                    <td>${property.street}</td>

                    <td>${property.area}</td>

                    <td>${property.city}</td>

                    <td>${property.property_type}</td>

                    <td>${property.no_of_rooms}</td>

                    <td>${property.monthly_rent}</td>

                </tr>

            `;

        });

    } catch (error) {

        console.error(error);

    }

}

loadRenters();

</script>

@endsection