@extends('layouts.main')

@section('title')
    Students Management
@endsection

@section('content')

    <h2>Import Students</h2>

    <form method="post" action="{{ url('/admin/students') }}" enctype="multipart/form-data">
        {{ csrf_field() }}

        <div class="form-group">
            <label for="students">Select file with students:</label>
            <input type="file" name="students">
        </div>

        <button type="submit" class="btn btn-default">
            <span class="glyphicon glyphicon-cloud-upload"></span> Upload Students List
        </button>
    </form>

    <h2>Students List</h2>

    <div class="container" style="margin-top: 20px">
        @if($students->count())

            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th class="text-center">AU ID</th>
                    <th class="text-center">Name</th>
                    <th class="text-center">Email</th>
                    <th class="text-center"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($students as $student)
                    <tr>
                        <td class="text-center">{{ $student->au_id }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ 'u' . $student->au_id . '@au.edu' }}</td>
                        <td class="text-center"> - </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

        @else
            <p>No students so far.</p>
        @endif
    </div>

@endsection