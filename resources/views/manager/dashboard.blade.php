<!DOCTYPE html>
<html>
<head><title>Manager Dashboard</title></head>
<body>
    <h1>Welcome, {{ Auth::user()->first_name }} (Manager)</h1>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>