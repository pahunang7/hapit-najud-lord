@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<div class="page-header">
    <div>
        <h1>Edit Branch</h1>
        <p>Update branch office details.</p>
    </div>
</div>

<div class="form-card">
    <form id="editBranchForm">
        <div class="form-grid">

            <div class="form-group">
                <label>Street</label>
                <input type="text" id="street" required>
            </div>

            <div class="form-group">
                <label>Area</label>
                <input type="text" id="area">
            </div>

            <div class="form-group">
                <label>City</label>
                <input type="text" id="city" required>
            </div>

            <div class="form-group">
                <label>Postcode</label>
                <input type="text" id="postcode" required>
            </div>

            <div class="form-group">
                <label>Telephone No</label>
                <input type="text" id="telephone_no" required>
            </div>

            <div class="form-group">
                <label>Fax No</label>
                <input type="text" id="fax_no">
            </div>

        </div>

        <div class="form-actions">
            <a href="/branches" class="cancel-btn">Cancel</a>
            <button type="submit" class="submit-btn">Update Branch</button>
        </div>
    </form>
</div>

<script>
const branchId = window.location.pathname.split('/')[2];

async function loadBranch() {
    try {
        const response = await fetch(`/api/branches/${branchId}`);
        const result = await response.json();

        if (!result.success) {
            alert('Failed to load branch');
            return;
        }

        const branch = result.data.branch;

        document.getElementById('street').value       = branch.street;
        document.getElementById('area').value         = branch.area ?? '';
        document.getElementById('city').value         = branch.city;
        document.getElementById('postcode').value     = branch.postcode;
        document.getElementById('telephone_no').value = branch.telephone_no;
        document.getElementById('fax_no').value       = branch.fax_no ?? '';

    } catch (error) {
        console.error(error);
    }
}

document.getElementById('editBranchForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const payload = {
        street:       document.getElementById('street').value,
        area:         document.getElementById('area').value,
        city:         document.getElementById('city').value,
        postcode:     document.getElementById('postcode').value,
        telephone_no: document.getElementById('telephone_no').value,
        fax_no:       document.getElementById('fax_no').value,
    };

    try {
        const response = await fetch(`/api/branches/${branchId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!response.ok) {
            alert(result.message ?? 'Update failed');
            return;
        }

        alert('Branch updated successfully');
        window.location.href = '/branches';

    } catch (error) {
        console.error(error);
    }
});

loadBranch();
</script>

@endsection