@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

<video class="banner-video" autoplay muted loop>
    <source src="{{ asset('videos/video.mp4.mp4') }}" type="video/mp4">
</video>



<div class="welcome-card">
    <h1>Welcome to DreamHome!</h1>

    <p class="welcome-text">
        This dashboar contains an overview of your rental properties, viewings, and leases. <br>
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