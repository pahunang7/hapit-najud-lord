<!DOCTYPE html>
<html>
<head>

 <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>DreamHome Module 4</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        nav a { margin-right: 15px; text-decoration: none; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        table, th, td { border: 1px solid #ccc; padding: 8px; }
        form { margin-top: 20px; }
        input, select { padding: 5px; margin: 5px 0; width: 100%; }
        button { padding: 8px 12px; cursor: pointer; }
    </style>
</head>
<body>

<nav>
    <a href="/properties">Properties</a>
    <a href="/viewings">Viewings</a>
    <a href="/leases">Leases</a>
</nav>

<hr>

@yield('content')

</body>
</html>