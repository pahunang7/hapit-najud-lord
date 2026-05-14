@extends('layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<div class="page-header">
    <div>
        <h1>Branch Offices</h1>
        <p>Manage DreamHome branch offices</p>
    </div>
    <a href="{{ route('branch.create') }}" class="add-btn">+ Add Branch</a>
</div>

{{-- SEARCH + FILTERS --}}
<div class="table-card search-card">
    <form method="GET" action="{{ route('branch.index') }}" class="search-form">

        <input
            type="text"
            name="search"
            placeholder="Search by city, street or postcode..."
            value="{{ request('search') }}"
        >

        {{-- FILTER: Branch dropdown --}}
        <select name="branch_no">
            <option value="">All Branches</option>
            @foreach(\App\Models\BranchOffice::orderBy('city')->get() as $b)
                <option value="{{ $b->branch_no }}"
                    {{ request('branch_no') == $b->branch_no ? 'selected' : '' }}>
                    {{ 'B' . str_pad($b->branch_no, 3, '0', STR_PAD_LEFT) }} — {{ $b->city }}
                </option>
            @endforeach
        </select>

        

        <button type="submit">Search</button>
        <a href="{{ route('branch.index') }}" class="cancel-btn">Clear</a>
    </form>
</div>

@if(session('success'))
    <div class="success-message">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="error-message">{{ session('error') }}</div>
@endif

{{-- TABLE --}}
<div class="table-card">
    <table class="client-table">
        <thead>
            <tr>
                <th>Branch No</th>
                <th>Street</th>
                <th>Area</th>
                <th>City</th>
                <th>Postcode</th>
                <th>Telephone</th>
                <th>Fax</th>
                <th>Staff Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($branches as $branch)
            <tr>
                <td>{{ 'B' . str_pad($branch->branch_no, 3, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $branch->street }}</td>
                <td>{{ $branch->area ?? '-' }}</td>
                <td>{{ $branch->city }}</td>
                <td>{{ $branch->postcode }}</td>
                <td>{{ $branch->telephone_no }}</td>
                <td>{{ $branch->fax_no ?? '-' }}</td>
                <td>{{ $branch->staff_count ?? 0 }}</td>
                <td class="action-buttons">
                    <a href="{{ route('branch.show', $branch) }}" class="view-btn">View</a>
                    <a href="{{ route('branch.edit', $branch) }}" class="edit-btn">Edit</a>
                    <button class="delete-btn" onclick="deleteBranch({{ $branch->branch_no }}, this)">Delete</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center;">No branch offices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination-wrapper">
    {{ $branches->appends(request()->query())->links() }}
</div>

<script>
async function deleteBranch(branchId, btn) {
    if (!confirm('Delete this branch? This cannot be undone.')) return;
    btn.disabled = true;
    btn.textContent = 'Deleting...';
    try {
        const response = await fetch(`/api/branches/${branchId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const result = await response.json();
        if (!result.success) {
            alert(result.message || 'Could not delete branch.');
            btn.disabled = false;
            btn.textContent = 'Delete';
            return;
        }
        btn.closest('tr').remove();
    } catch (error) {
        console.error(error);
        alert('Something went wrong.');
        btn.disabled = false;
        btn.textContent = 'Delete';
    }
}
</script>

@endsection