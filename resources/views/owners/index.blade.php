@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
@endsection

@section('content')

<div class="page-header">

    <h1>Owners</h1>

    <button class="add-btn" onclick="openModal()">
        + Add Owner
    </button>

</div>

<div class="table-container">

    <table class="owners-table">

        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>

            @forelse($owners as $owner)

            <tr>

                <td>{{ $owner->owner_no }}</td>

                <td>{{ $owner->full_name }}</td>

                <td>{{ $owner->address }}</td>

                <td>{{ $owner->telephone_no }}</td>

                <td>{{ $owner->email }}</td>

                <td class="actions">

                    <!-- EDIT BUTTON -->

                    <button
                        class="edit-btn"
                        onclick="editOwner(
                            '{{ $owner->owner_no }}',
                            '{{ $owner->full_name }}',
                            '{{ $owner->address }}',
                            '{{ $owner->telephone_no }}',
                            '{{ $owner->email }}'
                        )"
                    >
                        Edit
                    </button>

                    <!-- DELETE -->

                    <form
                        action="{{ route('owners.destroy', $owner->owner_no) }}"
                        method="POST"
                        class="delete-form"
                    >

                        @csrf
                        @method('DELETE')

                        <button type="submit" class="delete-btn">
                            Delete
                        </button>

                    </form>

                </td>

            </tr>

            @empty

            <tr>
                <td colspan="6" class="empty-text">
                    No owners yet.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>

</div>

<!-- OWNER MODAL -->

<div id="ownerModal" class="modal">

    <div class="modal-content">

        <span class="close-btn" onclick="closeModal()">
            &times;
        </span>

        <h2 id="modalTitle">Add Owner</h2>

        <form id="ownerForm"
              action="{{ route('owners.store') }}"
              method="POST">

            @csrf

            <div class="form-group">

    <label>Full Name</label>

    <input type="text"
           name="full_name"
           id="full_name"
           pattern="[A-Za-z\s]+"
           title="Only letters and spaces allowed"
           placeholder="e.g. Juan Dela Cruz"
           required>

</div>

<div class="form-group">

    <label>Address</label>

    <input type="text"
           name="address"
           id="address"
           minlength="5"
           placeholder="Enter Address"
           required>

</div>

<div class="form-group">

    <label>Email</label>

    <input type="email"
           name="email"
           id="email"
           placeholder="example@gmail.com"
           required>

</div>

<div class="form-group">

    <label>Telephone</label>

    <input type="text"
           name="telephone_no"
           id="telephone_no"
           pattern="[0-9]+"
           maxlength="11"
           minlength="11"
           title="Only 11-digit numbers allowed"
           required>

</div>

            <button type="submit" class="submit-btn">
                Save Owner
            </button>

        </form>

    </div>

</div>

<!-- DELETE CONFIRMATION MODAL -->

<div id="deleteModal" class="delete-modal">

    <div class="delete-modal-content">

        <div class="delete-icon">
            ⚠
        </div>

        <h2>Delete Owner?</h2>

        <p>
            This action cannot be undone.
        </p>

        <div class="delete-modal-buttons">

            <button id="cancelDelete"
                    class="cancel-delete-btn">

                Cancel

            </button>

            <button id="confirmDelete"
                    class="confirm-delete-btn">

                Delete

            </button>

        </div>

    </div>

</div>

<script>

let deleteForm = null;

// OPEN ADD MODAL

function openModal()
{
    document.getElementById('ownerModal').style.display = 'flex';

    document.getElementById('modalTitle').innerText = 'Add Owner';

    const form = document.getElementById('ownerForm');

    form.action = "{{ route('owners.store') }}";

    // REMOVE PUT METHOD IF EXISTS

    const method = document.getElementById('methodPUT');

    if (method) {
        method.remove();
    }

    // CLEAR INPUTS

    document.getElementById('full_name').value = '';
    document.getElementById('address').value = '';
    document.getElementById('telephone_no').value = '';
    document.getElementById('email').value = '';
}

// CLOSE OWNER MODAL

function closeModal()
{
    document.getElementById('ownerModal').style.display = 'none';
}

// EDIT OWNER

function editOwner(id, name, address, phone, email)
{
    document.getElementById('modalTitle').innerText = 'Edit Owner';

    document.getElementById('full_name').value = name;
    document.getElementById('address').value = address;
    document.getElementById('telephone_no').value = phone;
    document.getElementById('email').value = email;

    const form = document.getElementById('ownerForm');

    form.action = `/owners/${id}`;

    // ADD PUT METHOD

    if (!document.getElementById('methodPUT'))
    {
        let method = document.createElement('input');

        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PUT';
        method.id = 'methodPUT';

        form.appendChild(method);
    }

    document.getElementById('ownerModal').style.display = 'flex';
}

// DELETE MODAL

const deleteModal = document.getElementById('deleteModal');

// OPEN DELETE MODAL

document.querySelectorAll('.delete-form').forEach(form => {

    form.addEventListener('submit', function(e) {

        e.preventDefault();

        deleteForm = this;

        deleteModal.style.display = 'flex';
    });

});

// CONFIRM DELETE

document.getElementById('confirmDelete').addEventListener('click', function() {

    if (deleteForm) {
        deleteForm.submit();
    }

});

// CANCEL DELETE

document.getElementById('cancelDelete').addEventListener('click', function() {

    deleteModal.style.display = 'none';

});

// CLOSE WHEN CLICK OUTSIDE

window.onclick = function(event)
{
    const ownerModal = document.getElementById('ownerModal');

    if (event.target == ownerModal)
    {
        ownerModal.style.display = 'none';
    }

    if (event.target == deleteModal)
    {
        deleteModal.style.display = 'none';
    }
}

</script>

@endsection