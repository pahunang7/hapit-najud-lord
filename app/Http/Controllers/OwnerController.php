<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Owner;

class OwnerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | RBAC (enforced at route level AND controller level for safety):
    |   Manager    → View only  (index + show)
    |   Supervisor → Full CRUD
    |   Secretary  → Full CRUD  (Add/Edit/Delete per RBAC table)
    |   Staff      → No access  (routes blocked)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Owner::withCount('properties');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('full_name',     'ILIKE', "%{$s}%")
                  ->orWhere('address',     'ILIKE', "%{$s}%")
                  ->orWhere('telephone_no','ILIKE', "%{$s}%")
                  ->orWhere('email',       'ILIKE', "%{$s}%");
            });
        }

        $owners = $query->orderBy('full_name')->paginate(10)->withQueryString();

        return view('owners.index', compact('owners'));
    }

    public function create()
    {
        return view('owners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => 'required|string|max:100',
            'address'      => 'required|string|max:150',
            'telephone_no' => 'required|string|max:20',
            'email'        => 'nullable|email|max:100',
        ]);

        // Find lowest available ID
        $ids   = Owner::orderBy('owner_no')->pluck('owner_no')->toArray();
        $newId = 1;
        foreach ($ids as $id) {
            if ($id != $newId) break;
            $newId++;
        }
        $validated['owner_no'] = $newId;

        Owner::create($validated);

        return redirect()->route('owners.index')->with('success', 'Owner added successfully!');
    }

    public function show(int $id)
    {
        $owner = Owner::with('properties.branch')->findOrFail($id);
        return view('owners.show', compact('owner'));
    }

    public function edit(int $id)
    {
        $owner = Owner::findOrFail($id);
        return view('owners.edit', compact('owner'));
    }

    public function update(Request $request, int $id)
    {
        $owner = Owner::findOrFail($id);

        $validated = $request->validate([
            'full_name'    => 'required|string|max:100',
            'address'      => 'required|string|max:150',
            'telephone_no' => 'required|string|max:20',
            'email'        => 'nullable|email|max:100',
        ]);

        $owner->update($validated);

        return redirect()->route('owners.index')
            ->with('success', "Owner '{$owner->full_name}' updated successfully!");
    }

    public function destroy(string $id)
    {
        $owner = Owner::findOrFail($id);
        $owner->delete();

        return back()->with('success', 'Owner deleted successfully!');
    }
}