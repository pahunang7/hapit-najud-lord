@extends('layouts.app')

@section('content')


<h1>Dashboard</h1>

<p>Welcome to DreamHome Module 4</p>

<div style="display: flex; gap: 20px; margin-top: 20px;">

    <div style="border:1px solid #ccc; padding:15px;">
        <h3>Properties</h3>
        <p>View all available properties</p>
        <a href="/properties">Go →</a>
    </div>

    <div style="border:1px solid #ccc; padding:15px;">
        <h3>Viewings</h3>
        <p>Manage property viewings</p>
        <a href="/viewings">Go →</a>
    </div>

    <div style="border:1px solid #ccc; padding:15px;">
        <h3>Leases</h3>
        <p>Manage lease agreements</p>
        <a href="/leases">Go →</a>
    </div>

</div>

@endsection