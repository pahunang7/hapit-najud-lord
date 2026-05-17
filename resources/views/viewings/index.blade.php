@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<hr>

<div class="header-bar">
    <h2 class="vtitle">Viewings</h2>
    <button class="add-btn" onclick="openFormModal()">
        + Add Viewing
    </button>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<table id="viewingsTable">
    <thead class="vhead">
        <tr>
            <th>Property</th>
            <th>Renter</th>
            <th>Date</th>
            <th>Comments</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody></tbody>
</table>

<hr>

<!-- ================= FORM MODAL ================= -->
<div id="formModal" class="modal-overlay">

    <div class="modal-box">

        <h3 class="createv">Record Viewing</h3>

        <form id="viewingForm">

            <input type="hidden" id="editing_id">

            <!-- PROPERTY DROPDOWN -->
            <select id="property_no" required>
                <option value="">Select Property</option>
            </select>

            <!-- RENTER DROPDOWN -->
            <select id="renter_no" required>
                <option value="">Select Renter</option>
            </select>

            <input type="date" id="viewing_date" required>

            <input type="text" placeholder="Comments" id="comments">

            <button type="submit">Submit</button>

            <button type="button" onclick="closeFormModal()">
                Cancel
            </button>

        </form>
    </div>
</div>

<!-- ================= DELETE MODAL ================= -->
<div id="deleteModal" class="modal-overlay" style="display:none;">

    <div class="modal-box">

        <p class="modal-text">
            Are you sure you want to delete this viewing?
        </p>

        <div class="modal-actions">

            <button class="btn-delete" onclick="confirmDelete()">
                Delete
            </button>

            <button class="btn-cancel" onclick="closeDeleteModal()">
                Cancel
            </button>

        </div>
    </div>
</div>

<script>

let deleteData = {};

// ================= LOAD TABLE =================
function loadViewings() {

    fetch('/api/viewings', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
    })
    .then(res => res.json())
    .then(res => {

        const tbody = document.querySelector('#viewingsTable tbody');

        if (!res || !res.data || res.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5">No viewings yet.</td></tr>`;
            return;
        }

        let rows = '';

        res.data.forEach(v => {
            rows += `
            <tr>
                <td>${v.property_no} - ${v.property_address}</td>
                <td>${v.renter_no} - ${v.renter_name}</td>
                <td>${v.viewing_date}</td>
                <td>${v.comments ?? ''}</td>
                <td>
                    <button class="table-btne"
                        onclick="editViewing(${v.property_no}, ${v.renter_no}, '${v.viewing_date}')">
                        Edit
                    </button>
                    <button class="table-btnd"
                        onclick="openDeleteModal(${v.property_no}, ${v.renter_no}, '${v.viewing_date}')">
                        Delete
                    </button>
                </td>
            </tr>
            `;
        });

        tbody.innerHTML = rows;
    })
    .catch(err => {
        console.error(err);
        document.querySelector('#viewingsTable tbody').innerHTML =
            `<tr><td colspan="5">Error loading data</td></tr>`;
    });
}

// ================= LOAD DROPDOWNS =================
// /api/viewings/form-data returns {properties:[...], renters:[...]}
// This is the single source for both dropdowns — no separate loadRenters() needed.
// The old loadRenters() used member.staff_no as the option value for renters
// which was wrong; renter options must use renter_no.
async function loadFormData() {

    try {
        const res = await fetch('/api/viewings/form-data', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        });

        if (!res.ok) {
            console.error('Failed to load form data:', res.status);
            return;
        }

        const data = await res.json();

        // PROPERTY DROPDOWN
        const propertySelect = document.getElementById('property_no');
        propertySelect.innerHTML = `<option value="">Select Property</option>`;

        (data.properties ?? []).forEach(p => {
            propertySelect.innerHTML += `
                <option value="${p.property_no}">
                    #${p.property_no} - ${p.property_type} - ${p.street}, ${p.city}
                </option>
            `;
        });

        // RENTER DROPDOWN — use renter_no (not staff_no)
        const renterSelect = document.getElementById('renter_no');
        renterSelect.innerHTML = `<option value="">Select Renter</option>`;

        (data.renters ?? []).forEach(r => {
            renterSelect.innerHTML += `
                <option value="${r.renter_no}">
                    #${r.renter_no} - ${r.renter_name}
                </option>
            `;
        });

    } catch (err) {
        console.error('loadFormData error:', err);
    }
}

// ================= OPEN MODAL =================
function openFormModal(reset = true) {
    if (reset) cancelViewing();
    document.getElementById('formModal').style.display = 'flex';
}

// ================= CLOSE MODAL =================
function closeFormModal() {
    document.getElementById('formModal').style.display = 'none';
    cancelViewing();
}

// ================= RESET FORM =================
function cancelViewing() {
    document.getElementById('viewingForm').reset();
    document.getElementById('editing_id').value = '';
    document.querySelector('#viewingForm button[type="submit"]').textContent = 'Submit';
    document.querySelector('.createv').textContent = 'Record Viewing';
}

// ================= EDIT =================
function editViewing(property_no, renter_no, viewing_date) {

    fetch(`/api/viewings/${property_no}/${renter_no}/${viewing_date}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
    })
    .then(res => res.json())
    .then(res => {

        const v = res.data;

        document.getElementById('editing_id').value  = `${property_no}|${renter_no}|${viewing_date}`;
        document.getElementById('property_no').value = v.property_no;
        document.getElementById('renter_no').value   = v.renter_no;
        document.getElementById('viewing_date').value = v.viewing_date;
        document.getElementById('comments').value    = v.comments ?? '';

        document.querySelector('#viewingForm button[type="submit"]').textContent = 'Update Viewing';
        document.querySelector('.createv').textContent = 'Edit Viewing';

        openFormModal(false);
    });
}

// ================= DELETE =================
function openDeleteModal(property_no, renter_no, viewing_date) {
    deleteData = { property_no, renter_no, viewing_date };
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    deleteData = {};
}

// ================= CONFIRM DELETE =================
function confirmDelete() {

    fetch(`/api/viewings/${deleteData.property_no}/${deleteData.renter_no}/${deleteData.viewing_date}`, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(async res => {

        const response = await res.json();

        if (!res.ok) {
            showMessage(response.message || 'Delete failed', 'error');
            return;
        }

        showMessage(response.message, 'success');
        loadViewings();
        closeDeleteModal();
    })
    .catch(() => showMessage('Delete failed', 'error'));
}

// ================= SUBMIT =================
document.getElementById('viewingForm').addEventListener('submit', async function (e) {

    e.preventDefault();

    const id = document.getElementById('editing_id').value;

    let url    = '/api/viewings';
    let method = 'POST';

    if (id) {
        const [property_no, renter_no, viewing_date] = id.split('|');
        url    = `/api/viewings/${property_no}/${renter_no}/${viewing_date}`;
        method = 'PUT';
    }

    const data = {
        property_no:  document.getElementById('property_no').value,
        renter_no:    document.getElementById('renter_no').value,
        viewing_date: document.getElementById('viewing_date').value,
        comments:     document.getElementById('comments').value,
    };

    try {
        const res = await fetch(url, {
            method:      method,
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        });

        const response = await res.json();

        if (!res.ok) {
            if (res.status === 422 && response.errors) {
                const messages = Object.values(response.errors).flat().join('\n');
                showMessage(messages, 'error');
                return;
            }
            showMessage(response.message || 'Something went wrong.', 'error');
            return;
        }

        showMessage(id ? 'Updated successfully!' : 'Created successfully!', 'success');
        closeFormModal();
        loadViewings();

    } catch (err) {
        console.error(err);
        showMessage('Network error. Check your connection.', 'error');
    }
});

// ================= CLICK OUTSIDE =================
window.onclick = function (e) {
    if (e.target === document.getElementById('formModal'))   closeFormModal();
    if (e.target === document.getElementById('deleteModal')) closeDeleteModal();
};

// ================= TOAST =================
function showMessage(message, type = 'success') {

    const box = document.createElement('div');
    box.innerText = message;

    box.style.cssText = `
        position: fixed; top: 20px; right: 20px;
        padding: 12px 20px; color: #fff;
        border-radius: 6px; z-index: 9999;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        background: ${type === 'error' ? '#e74c3c' : '#2ecc71'};
    `;

    document.body.appendChild(box);
    setTimeout(() => box.remove(), 3000);
}

// ================= INIT =================
loadViewings();
loadFormData();

</script>

@endsection