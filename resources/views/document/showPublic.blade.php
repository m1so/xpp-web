@extends('layouts.app')

@section('title', $document->title.' (public)')
@section('content-title', $document->title.' (public)')

@section('content')
    <div class="col-md-12">
        <pre>{{ $document->files['ode'] }}</pre>
    </div>
@endsection
