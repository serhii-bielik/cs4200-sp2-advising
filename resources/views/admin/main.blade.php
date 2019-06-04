@extends('layouts.main')

@section('title')
    Full System Reset
@endsection

@section('content')

    <div class="container" style="margin-top: 20px">
        <h3 class="bg-danger text-center">Warning! All data will be removed from the system and it will be initialized from scratch.</h3>
        <p class="text-center"><a href="{{ url('/admin/systemReset') }}" class="btn btn-lg btn-danger" rel="nofollow" onclick="return confirm('This action cannot be revert. Are you sure?')">
                <span class="glyphicon glyphicon-remove-sign"></span> Reset System</a>
        </p>
    </div>

@endsection