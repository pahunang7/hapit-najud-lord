@extends('layouts.app')

@section('content')

<div class="page-header">

    <div>
        <h1>Edit Client</h1>
        <p>Update client information.</p>
    </div>

</div>

<div class="form-card">

    <form id="editClientForm">

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

                <select id="branch_no"></select>
            </div>

            <!-- STAFF -->
            <div class="form-group">
                <label>Staff</label>

                <select id="staff_no"></select>
            </div>

        </div>

        <div class="form-actions">

            <a href="/renter" class="cancel-btn">
                Cancel
            </a>

            <button type="submit" class="submit-btn">
                Update Client
            </button>

        </div>

    </form>

</div>

<script>

const renterId = window.location.pathname.split('/')[2];

async function loadBranches(selectedBranch = null) {

    const response =
        await fetch('/api/branches');

    const result =
        await response.json();

    const branches = result.data;

    const branchSelect =
        document.getElementById('branch_no');

    branchSelect.innerHTML = '';

    branches.forEach(branch => {

        branchSelect.innerHTML += `
            <option
                value="${branch.branch_no}"
                ${selectedBranch == branch.branch_no ? 'selected' : ''}>
                ${branch.branch_no} - ${branch.city}
            </option>
        `;

    });

}

async function loadStaff(branchNo, selectedStaff = null) {

    const response =
        await fetch(`/api/staff?branch_no=${branchNo}`);

    const result =
        await response.json();

    const staff = result.data;

    const staffSelect =
        document.getElementById('staff_no');

    staffSelect.innerHTML = '';

    staff.forEach(member => {

        staffSelect.innerHTML += `
    <option value="${member.staff_no}" ${selectedStaff == member.staff_no ? 'selected' : ''}>
        ${'S' + String(member.staff_no).padStart(3, '0')} — ${member.first_name} ${member.last_name}
    </option>
`;

    });

}

async function loadClient() {

    try {

        const response =
            await fetch(`/api/renters/${renterId}`);

        const result =
            await response.json();

        const renter = result.data;

        document.getElementById('first_name').value =
            renter.first_name;

        document.getElementById('last_name').value =
            renter.last_name;

        document.getElementById('address').value =
            renter.address;

        document.getElementById('telephone_no').value =
            renter.telephone_no;

        document.getElementById('preferred_type').value =
            renter.preferred_type;

        document.getElementById('preferred_location').value =
            renter.preferred_location;

        document.getElementById('max_rent').value =
            renter.max_rent;

        await loadBranches(renter.branch_no);

        await loadStaff(
            renter.branch_no,
            renter.staff_no
        );

    } catch (error) {

        console.error(error);

    }

}

document
.getElementById('branch_no')
.addEventListener('change', function () {

    loadStaff(this.value);

});

document
.getElementById('editClientForm')
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

        const response = await fetch(
            `/api/renters/${renterId}`,
            {

                method: 'PUT',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },

                body: JSON.stringify(payload)

            }
        );

        const result = await response.json();

        if (!response.ok) {

            alert(result.message);

            return;
        }

        alert('Client updated successfully.');

        window.location.href = '/renter';

    } catch (error) {

        console.error(error);

    }

});

loadClient();

</script>

@endsection