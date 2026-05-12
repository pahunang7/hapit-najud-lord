@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<video class="banner-video" autoplay muted loop>
    <source src="{{ asset('videos/video.mp4.mp4') }}" type="video/mp4">
</video>


<div class="welcome-card">
    <h1>Welcome to DreamHome!</h1>

    <p class="welcome-text">
        This dashboard contains an overview of your rental properties, viewings, and leases.
        Use the navigation links above to explore the different sections and manage your rental experience with ease.</p>


</div>

<hr>



<div class="stats">

    <div class="stat-box">
    <h2>{{ $propertyCount }}</h2>
    <p>Properties</p>
</div>

<div class="stat-box">
    <h2>{{ $viewingCount }}</h2>
    <p>Viewings</p>
</div>

<div class="stat-box">
    <h2>{{ $activeLeaseCount }}</h2>
    <p>Active Leases</p>
</div>

</div>

@endsection