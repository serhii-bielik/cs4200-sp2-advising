@extends('layouts.main')

@section('title')
    Advisers Management
@endsection

@section('content')

    <h2>Import Advisers</h2>

    <form method="post" action="{{ url('/admin/advisers') }}" enctype="multipart/form-data">
        {{ csrf_field() }}

        <div class="form-group">
            <label for="advisers">Select file with advisers:</label>
            <input type="file" name="advisers">
        </div>

        <button type="submit" class="btn btn-default">
            <span class="glyphicon glyphicon-cloud-upload"></span> Upload Advisers List
        </button>
    </form>

    <h2>Advisers List</h2>

    <div class="container" style="margin-top: 20px">
        @if($advisers->count())

                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th class="text-center">AU ID</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Group</th>
                        <th class="text-center">Faculty</th>
                        <th class="text-center">Manage</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($advisers as $adviser)
                            <tr>
                                <td class="text-center">{{ $adviser->au_id }}</td>
                                <td>{{ $adviser->name }}</td>
                                <td>{{ $adviser->email }}</td>
                                <td class="text-center">{{ $adviser->group->name }}</td>
                                <td class="text-center">{{ $adviser->faculty->code }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-info btn-xs"
                                            onclick="editUser({{ $adviser->id }})">
                                        <span class=" 	glyphicon glyphicon-pencil"></span> Edit
                                    </button>&nbsp;
                                    <button type="button" class="btn btn-danger btn-xs"
                                            onclick="rmUser({{ $adviser->id }})">
                                        <span class="glyphicon glyphicon-remove"></span> Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

        @else
            <p>No advisers so far.</p>
        @endif
    </div>

@endsection

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