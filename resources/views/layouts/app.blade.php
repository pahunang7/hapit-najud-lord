<!DOCTYPE html>
<html>
<head>

 <meta name="csrf-token" content="{{ csrf_token() }}">
 <link rel="stylesheet" href="css/style.css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
 <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
 

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

<a href="/properties" class="{{ request()->is('properties*') ? 'active' : '' }}">
    <i class="fa-solid fa-building"></i> List of Properties
</a>

<a href="/viewings" class="{{ request()->is('viewings*') ? 'active' : '' }}">
    <i class="fa-solid fa-calendar-check"></i> Viewings
</a>

<a href="/leases" class="{{ request()->is('leases*') ? 'active' : '' }}">
    <i class="fa-solid fa-file-contract"></i> Lease Agreements
</a>

<a href="/renter" class="{{ request()->is('renter*') ? 'active' : '' }}">
    <i class="fa-solid fa-user-plus"></i> Renter Registration
</a>
</aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            @yield('content')
        </main>

    </div>
</body>

</body>
</html>