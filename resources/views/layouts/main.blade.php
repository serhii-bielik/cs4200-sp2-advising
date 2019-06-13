<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - Advising Scheduling System</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
{{--    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>--}}
    <script src="https://cdn.jsdelivr.net/npm/axios@0.12.0/dist/axios.min.js"></script>
</head>
<body>

<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="/">Basic System Management</a>
        </div>
        <ul class="nav navbar-nav">
            <li class="{{ Request::is('admin') ? 'active' : '' }}"><a href="{{ url('/admin') }}">Advisers</a></li>
            <li class="{{ Request::is('admin/students') ? 'active' : '' }}"><a href="{{ url('/admin/students') }}">Students</a></li>
            <li class="{{ Request::is('admin/usersRemoval') ? 'active' : '' }}"><a href="{{ url('/admin/usersRemoval') }}">Mass Users Removal</a></li>
            <li class="{{ Request::is('admin/system') ? 'active' : '' }}"><a href="{{ url('/admin/system') }}"><span class="text-danger">System Reset</span></a></li>
        </ul>
    </div>
</nav>

<div class="container">

    @if(session('message'))
        <p class="alert {{ session('alert-class', 'alert-info') }} alert-dismissible">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <strong>{{ session('message') }}</strong>
        </p>
    @endif

    <h1>@yield('title')</h1>
    @yield('content')
</div>

</body>
</html>
