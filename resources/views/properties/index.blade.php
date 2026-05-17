@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<hr>

<div class="header-bar">
    <h2 class="ptitle">Properties</h2>

    <button class="add-btn" onclick="openModal()">
        + Add Property
    </button>
</div>

<hr>

<div class="table-container">
    <table id="propertiesTable">
        <thead class="phead">
            <tr>
                <th>ID</th>
                <th>Address</th>
                <th>Type</th>
                <th>Rooms</th>
                <th>Rent</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($properties as $p)
            <tr>
                <td>{{ $p->property_no }}</td>

                <td>
                    {{ $p->street }},
                    {{ $p->area }},
                    {{ $p->city }}
                    {{ $p->postcode }}
                </td>

                <td>{{ $p->property_type }}</td>

                <td>{{ $p->no_of_rooms }}</td>

                <td>{{ $p->monthly_rent }}</td>

                <td>
                    <span class="status available">
                        Available
                    </span>
                </td>

                <td>
                    <div class="actions">

                        <button class="edit-btn">
                            Edit
                        </button>

                        <form action="/properties/{{ $p->property_no }}" method="POST">

                            @csrf
                            @method('DELETE')

                            <button type="submit" class="delete-btn">
                                Delete
                            </button>

                        </form>

                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- ADD PROPERTY MODAL -->

<div id="propertyModal" class="modal">

    <div class="modal-content property-modal">

        <span class="close-btn" onclick="closeModal()">&times;</span>

        <h2>Add Property</h2>

        <form action="/properties" method="POST">

            @csrf

            <div class="form-group">
                <label>Street</label>
                <input type="text" name="street" required>
            </div>

            <div class="form-group">
                <label>Area</label>
                <input type="text" name="area" required>
            </div>

            <div class="form-group">
                <label>City</label>
                <input type="text" name="city" required>
            </div>

            <div class="form-group">
                <label>Postcode</label>
                <input type="text" name="postcode" required>
            </div>

            <div class="form-group">
                <label>Property Type</label>
                <select name="property_type" required>
                    <option value="Flat">Flat</option>
                    <option value="House">House</option>
                    <option value="Apartment">Apartment</option>
                </select>
            </div>

            <div class="form-group">
                <label>Rooms</label>
                <input type="number" name="no_of_rooms" min="1" required>
            </div>

            <div class="form-group">
                <label>Monthly Rent</label>
                <input type="number" name="monthly_rent" min="1" required>
            </div>

            <div class="form-group">
                <label>Owner</label>
                <select name="owner_no" required>
                    <option value="">Select Owner</option>
                    @foreach($owners as $owner)
                        <option value="{{ $owner->owner_no }}">
                            {{ $owner->owner_no }} - {{ $owner->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Branch</label>
                <select name="branch_no" id="branchSelect" required>
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->branch_no }}">
                            Branch #{{ $branch->branch_no }} — {{ $branch->city }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Staff</label>
                <select name="staff_no" id="staffSelect" required>
                    <option value="">Select a branch first</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">
                Add Property
            </button>

        </form>
    </div>
</div>

<script>

function openModal() {
    document.getElementById('propertyModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('propertyModal').style.display = 'none';
}

window.onclick = function (event) {
    const modal = document.getElementById('propertyModal');
    if (event.target === modal) closeModal();
};

/* =========================================================
   LOAD STAFF ON BRANCH CHANGE
   Endpoint: /api/branches/{branchNo}/staff
   BranchOfficeController::getStaff returns a plain array.
   We guard against {data:[]} shape too just in case.
========================================================= */
document.getElementById('branchSelect').addEventListener('change', async function () {

    const branchNo    = this.value;
    const staffSelect = document.getElementById('staffSelect');

    staffSelect.innerHTML = '<option value="">Loading...</option>';

    if (!branchNo) {
        staffSelect.innerHTML = '<option value="">Select a branch first</option>';
        return;
    }

    try {
        const response = await fetch(`/api/branches/${branchNo}/staff`, {
            credentials: 'same-origin',
            headers: {
                'Accept':            'application/json',
                'X-Requested-With':  'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            staffSelect.innerHTML = '<option value="">Failed to load staff</option>';
            return;
        }

        const result = await response.json();

        // getStaff returns a plain array; guard against {data:[]} shape too
        const staff = Array.isArray(result) ? result : (result.data ?? []);

        if (staff.length === 0) {
            staffSelect.innerHTML = '<option value="">No staff found for this branch</option>';
            return;
        }

        staffSelect.innerHTML = '<option value="">Select Staff</option>';

        staff.forEach(member => {
            const staffName = member.full_name
                ?? (member.first_name + ' ' + member.last_name);

            const option       = document.createElement('option');
            option.value       = member.staff_no;
            option.textContent = `${member.staff_no} - ${staffName}`;
            staffSelect.appendChild(option);
        });

    } catch (err) {
        console.error('Failed to load staff:', err);
        staffSelect.innerHTML = '<option value="">Failed to load staff</option>';
    }
});

</script>

@endsection