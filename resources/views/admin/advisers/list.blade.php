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
                        <th class="text-center"></th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($advisers as $adviser)
                            <tr>
                                <td class="text-center">{{ $adviser->au_id }}</td>
                                <td>{{ $adviser->name }}</td>
                                <td>{{ $adviser->email }}</td>
                                <td class="text-center">{{ $adviser->group_id }}</td>
                                <td class="text-center"> - </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

        @else
            <p>No advisers so far.</p>
        @endif
    </div>

@endsection