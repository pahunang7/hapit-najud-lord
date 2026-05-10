<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;

class OwnerController extends Controller
{
     public function index(Request $request)
    {
        $query = Owner::withCount('properties');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('full_name',    'ILIKE', "%{$s}%")
                  ->orWhere('address',    'ILIKE', "%{$s}%")
                  ->orWhere('telephone_no','ILIKE', "%{$s}%")
                  ->orWhere('email',      'ILIKE', "%{$s}%");
            });
        }

        $owners = $query->orderBy('full_name')->paginate(10)->withQueryString();
        return view('owners.index', compact('owners'));
    }

    // CREATE FORM
    public function create()
    {
        return view('owners.create');
    }

    // STORE
public function store(Request $request)
{
    $validated = $request->validate([
        'full_name'    => 'required|string|max:100',
        'address'      => 'required|string|max:150',
        'telephone_no' => 'required|string|max:20',
        'email'        => 'nullable|email|max:100',
    ]);

    // FIND LOWEST AVAILABLE ID

    $ids = Owner::orderBy('owner_no')->pluck('owner_no')->toArray();

    $newId = 1;

    foreach ($ids as $id)
    {
        if ($id != $newId) {
            break;
        }

        $newId++;
    }

    $validated['owner_no'] = $newId;

    Owner::create($validated);

    return redirect()->route('owners.index');
}
    // SHOW
    public function show(int $id)
    {
        $owner = Owner::with('properties.branch')->findOrFail($id);
        return view('owners.show', compact('owner'));
    }

    // EDIT FORM
    public function edit(int $id)
    {
        $owner = Owner::findOrFail($id);
        return view('owners.edit', compact('owner'));
    }

    // UPDATE
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

    // DELETE
    public function destroy(string $id)
{
    $owner = Owner::findOrFail($id);

    $owner->delete();

    return back();
}
}
