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

    <!-- Modal -->
    <div id="editUser" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit User Details</h4>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="uname">Name:</label>
                        <input type="text" required class="form-control" id="uname">
                    </div>
                    <div class="form-group">
                        <label for="auid">AU ID:</label>
                        <input type="text" required class="form-control" id="auid">
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" required class="form-control" id="email">
                    </div>
                    <div class="form-group">
                        <label for="group">Group:</label>
                        <select class="form-control" id="group">
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="faculty">Faculty:</label>
                        <select class="form-control" id="faculty">
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" required class="form-control" id="uid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="updateUser()">
                        <span class="glyphicon glyphicon-ok"></span> Save
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>

    function updateUser() {
        const id = $("#uid").val();
        const name = $("#uname").val();
        const au_id = $("#auid").val();
        const email = $("#email").val();
        const group_id = $("#group").val();
        const faculty_id = $("#faculty").val();

        axios.post('/api/admin/userData',{
            id: id,
            name: name,
            au_id: au_id,
            email: email,
            group_id: group_id,
            faculty_id: faculty_id,
        }).then(response => location.reload())
            .catch(error => alert(error.data.error));
    }

    function editUser(userId) {
        axios.get('/api/admin/userData/' + userId,{
        }).then(response => {

            $("#uid").val(response.data.id);
            $("#uname").val(response.data.name);
            $("#auid").val(response.data.au_id);
            $("#email").val(response.data.email);
            $("#group").val(response.data.group_id);
            $("#faculty").val(response.data.faculty_id);

            $("#editUser").modal();

        }).catch(error => alert(error.data.error));
    }

    function rmUser(userId) {
        if (confirm('All data related to this user will be removed. This action cannot be revert. Are you sure?')) {
            axios.post('/api/admin/removeUser',{
                user_id: userId,
            }).then(response => location.reload())
                .catch(error => alert(error.data.error));
        }
    }
</script>

</body>
</html>
