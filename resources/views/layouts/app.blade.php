<!DOCTYPE html>
<html>
<head>

<head>

<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<link rel="stylesheet" href="{{ asset('css/property-search.css') }}">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

@yield('styles')

<title>DreamHome</title>

</head>
 

    <title>DreamHome</title>

</head>
<body>
    <div class="layout">
        
        <!-- SIDEBAR -->
        <aside class="sidebar">
    <h2><i class="fa-solid fa-house"></i> DreamHome</h2>

    <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
    <i class="fa-solid fa-table-columns"></i> Dashboard
</a>

<a href="/owner" class="{{ request()->is('owner*') ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i> Owners
            </a>

<a href="/branches" class="{{ request()->is('branches*') ? 'active' : '' }}">
    <i class="fa-solid fa-code-branch"></i> Branches
</a>

<a href="/staff" class="{{ request()->is('staff*') ? 'active' : '' }}">
    <i class="fa-solid fa-users"></i> Staff
</a>
            
<a href="/properties" class="{{ request()->is('properties*') ? 'active' : '' }}">
    <i class="fa-solid fa-building"></i> Properties
</a>

<a href="/viewings" class="{{ request()->is('viewings*') ? 'active' : '' }}">
    <i class="fa-solid fa-calendar-check"></i> Viewings
</a>

<a href="/leases" class="{{ request()->is('leases*') ? 'active' : '' }}">
    <i class="fa-solid fa-file-contract"></i> Lease Agreements
</a>

<a href="/renter" class="{{ request()->is('renter') ? 'active' : '' }}">
    <i class="fa-solid fa-user-plus"></i> Client List
</a>

<a href="/renter/create" class="{{ request()->is('renter/create') ? 'active' : '' }}">
    <i class="fa-solid fa-user-plus"></i> Renter Registration
</a>

<a href="/renter/search" class="{{ request()->is('renter/search') ? 'active' : '' }}">
    <i class="fa-solid fa-magnifying-glass"></i> Search Properties
</a>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="logout-btn">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </button>
</form>

</aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            @yield('content')
        </main>

    </div>
</body>

</body>
</html>