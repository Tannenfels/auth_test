@extends('layouts.app')

@section('content')

    @if($errMsg)
        {{ $errMsg }}<br/>
    @endif
@endsection
