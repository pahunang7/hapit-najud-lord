@extends('layouts.app')

@section('content')

<video class="banner-video" autoplay muted loop>
    <source src="{{ asset('videos/video.mp4.mp4') }}" type="video/mp4">
</video>

<hr>

<div class="welcome-card">
    <h1>Welcome to DreamHome!</h1>

    <p class="welcome-text">
    This dashboard provides an overview of our rental properties, viewings, and leases. 
    Use the navigation links above to explore the different sections and manage your rental experience with ease.</p>


</div>

<hr>

<div class="dashboard-section">

    <div class="card">
        <a href="/properties">View and manage available listings</a>
    </div>

    <div class="card">
        <a href="/viewings">Track and record property viewings </a>
    </div>

    <div class="card">
        <a href="/leases">Manage lease agreements</a>
    </div>

</div>

<div class="stats">

    <div class="stat-box">
        <h2>12</h2>
        <p>Properties</p>
    </div>

    <div class="stat-box">
        <h2>8</h2>
        <p>Viewings</p>
    </div>

    <div class="stat-box">
        <h2>5</h2>
        <p>Active Leases</p>
    </div>

</div>

@endsection