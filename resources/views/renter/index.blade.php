@extends('layouts.app')

@section('content')

<div class="page-header">

    <div>
        <h1>Registered Clients</h1>
        <p>List of all registered clients</p>
    </div>

    <a href="/renter/create" class="add-client-btn">+ Register Client</a>

</div>

<div class="table-card">

    <table class="client-table">

        <thead>
            <tr>
                <th>Renter No</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Address</th>
                <th>Telephone</th>
                <th>Preferred Type</th>
                <th>Preferred Location</th>
                <th>Max Rent</th>
                <th>Staff No</th>
                <th>Branch No</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="clientTableBody">

        </tbody>

    </table>

</div>

<script>

async function loadClients() {

    try {

        const response = await fetch('/api/renters');

        const result = await response.json();

        const renters = result.data;

        const tableBody = document.getElementById('clientTableBody');

        tableBody.innerHTML = '';

        renters.forEach(renter => {

            tableBody.innerHTML += `
                <tr>

                    <td>${renter.renter_no}</td>
                    <td>${renter.first_name}</td>
                    <td>${renter.last_name}</td>
                    <td>${renter.address}</td>
                    <td>${renter.telephone_no}</td>
                    <td>${renter.preferred_type ?? ''}</td>
                    <td>${renter.preferred_location ?? ''}</td>
                    <td>${renter.max_rent ?? ''}</td>
                    <td>${renter.staff_no}</td>
                    <td>${renter.branch_no}</td>

                    <td class="action-buttons">

                        <a href="/renter/${renter.renter_no}/edit"
                           class="edit-btn">
                            Edit
                        </a>

                        <button
                            class="delete-btn"
                            onclick="deleteClient(${renter.renter_no})">

                            Delete

                        </button>

                    </td>

                </tr>
            `;

        });

    } catch (error) {

        console.error(error);

    }

}

async function deleteClient(id) {

    const confirmed = confirm(
        'Are you sure you want to delete this client?'
    );

    if (!confirmed) return;

    try {

        const response = await fetch(`/api/renters/${id}`, {

            method: 'DELETE',

            headers: {
                'Accept': 'application/json'
            }

        });

        const result = await response.json();

        alert(result.message);

        loadClients();

    } catch (error) {

        console.error(error);

    }

}

loadClients();

</script>

@endsection