<!DOCTYPE html>
<html>
<head>

 <meta name="csrf-token" content="{{ csrf_token() }}">
 <link rel="stylesheet" href="css/style.css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
 

    <title>DreamHome</title>

</head>
<body>

<nav class="navbar">

    <div class="nav-left">
        <i class="fa-solid fa-house"></i>
        <span class="logo-text">DreamHome</span>
    </div>

    
    <div class="nav-links">
        <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i> Home
        </a>

        <a href="/properties" class="{{ request()->is('properties') ? 'active' : '' }}">
            <i class="fa-solid fa-building"></i> Properties
        </a>

        <a href="/viewings" class="{{ request()->is('viewings') ? 'active' : '' }}">
            <i class="fa-solid fa-eye"></i> Viewings
        </a>

        <a href="/leases" class="{{ request()->is('leases') ? 'active' : '' }}">
            <i class="fa-solid fa-file-contract"></i> Leases
        </a>
    </div>

</nav>

@yield('content')

</body>
</html>