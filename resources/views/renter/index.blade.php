@extends('layouts.app')

@section('content')

<div class="page-header">

    <div>
        <h1>Register Client</h1>
        <p>Register a new client looking for a property.</p>
    </div>

</div>

<div class="form-card">

    <form id="registerClientForm">

        <div class="form-grid">

            <!-- FIRST NAME -->
            <div class="form-group">
                <label>First Name</label>

                <input
                    type="text"
                    id="first_name"
                    required
                >
            </div>

            <!-- LAST NAME -->
            <div class="form-group">
                <label>Last Name</label>

                <input
                    type="text"
                    id="last_name"
                    required
                >
            </div>

            <!-- ADDRESS -->
            <div class="form-group">
                <label>Address</label>

                <input
                    type="text"
                    id="address"
                    required
                >
            </div>

            <!-- TELEPHONE -->
            <div class="form-group">
                <label>Telephone</label>

                <input
                    type="text"
                    id="telephone_no"
                    required
                >
            </div>

            <!-- PREFERRED TYPE -->
            <div class="form-group">
                <label>Preferred Type</label>

                <select id="preferred_type">

                    <option value="">
                        Select Type
                    </option>

                    <option value="Apartment">
                        Apartment
                    </option>

                    <option value="House">
                        House
                    </option>

                    <option value="Condo">
                        Condo
                    </option>

                </select>
            </div>

            <!-- PREFERRED LOCATION -->
            <div class="form-group">
                <label>Preferred Location</label>

                <input
                    type="text"
                    id="preferred_location"
                >
            </div>

            <!-- MAX RENT -->
            <div class="form-group">
                <label>Max Rent</label>

                <input
                    type="number"
                    id="max_rent"
                    min="0"
                >
            </div>

            <!-- BRANCH -->
            <div class="form-group">
                <label>Branch</label>

                <select id="branch_no" required>

                    <option value="">
                        Select Branch
                    </option>

                </select>
            </div>

            <!-- STAFF -->
            <div class="form-group">
                <label>Staff</label>

                <select id="staff_no" required>

                    <option value="">
                        Select Staff
                    </option>

                </select>
            </div>

        </div>

        <div class="form-actions">

            <a href="/clients" class="cancel-btn">
                Cancel
            </a>

            <button type="submit" class="submit-btn">
                Register Client
            </button>

        </div>

    </form>

</div>

<script>

async function loadBranches() {

    const response = await fetch('/api/branches');

    const result = await response.json();

    const branches = result.data;

    const branchSelect =
        document.getElementById('branch_no');

    branches.forEach(branch => {

        branchSelect.innerHTML += `
            <option value="${branch.branch_no}">
                ${branch.branch_no} - ${branch.city}
            </option>
        `;

    });

}

async function loadStaff(branchNo) {

    const response = await fetch(
        `/api/staff?branch_no=${branchNo}`
    );

    const result = await response.json();

    const staff = result.data;

    const staffSelect =
        document.getElementById('staff_no');

    staffSelect.innerHTML = `
        <option value="">
            Select Staff
        </option>
    `;

    staff.forEach(member => {

        staffSelect.innerHTML += `
            <option value="${member.staff_no}">
                ${member.full_name}
            </option>
        `;

    });

}

document
.getElementById('branch_no')
.addEventListener('change', function () {

    loadStaff(this.value);

});

document
.getElementById('registerClientForm')
.addEventListener('submit', async function (e) {

    e.preventDefault();

    const payload = {

        first_name:
            document.getElementById('first_name').value,

        last_name:
            document.getElementById('last_name').value,

        address:
            document.getElementById('address').value,

        telephone_no:
            document.getElementById('telephone_no').value,

        preferred_type:
            document.getElementById('preferred_type').value,

        preferred_location:
            document.getElementById('preferred_location').value,

        max_rent:
            document.getElementById('max_rent').value,

        branch_no:
            document.getElementById('branch_no').value,

        staff_no:
            document.getElementById('staff_no').value

    };

    try {

        const response = await fetch('/api/renters', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },

            body: JSON.stringify(payload)

        });

        const result = await response.json();

        if (!response.ok) {

            console.log(result);

            alert(result.message);

            return;
        }

        alert('Client registered successfully.');

        window.location.href = '/renter';

    } catch (error) {

        console.error(error);

    }

});

loadBranches();

</script>

@endsection