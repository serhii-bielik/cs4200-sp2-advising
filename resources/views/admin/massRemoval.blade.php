@extends('layouts.main')

@section('title')
    Mass Users Removal
@endsection

@section('content')

    <form method="post" action="{{ url('/admin/userRemoval') }}" enctype="multipart/form-data">
        {{ csrf_field() }}

        <p>
            <strong><span class="text-danger">All provided users and data related to them will be permanently removed.</span></strong><br>
            Sample file format: <strong><a href="{{ url('/samples/users.xlsx') }}">users.xlsx</a></strong>
        </p>
        <div class="form-group">
            <label for="users">Select file with users to remove:</label>
            <input type="file" required name="users">
        </div>

        <button type="submit" class="btn btn-default">
            <span class="glyphicon glyphicon-cloud-upload"></span> Upload And Remove Users
        </button>
    </form>

@endsection