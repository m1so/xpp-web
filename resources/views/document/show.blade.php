@extends('layouts.app')

@section('title', $document->title)
@section('content-title', $document->title)

@section('content')
    <document
            ode="{{ $document->odeFile() }}"
            log="{{ $document->logFile() }}"
            output="{{ $document->resultFile() }}"
            :data="{{ $document->toJson() }}"
    >
    </document>
@endsection