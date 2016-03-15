@extends('layouts.app')

@section('title', $document->title)
@section('content-title', $document->title)

@section('content')
    <document-switcher
            ode="{{ $document->odeFile() }}"
            log="{{ $document->logFile() }}"
            output="{{ $document->resultFile() }}"
            title="{{ $document->title }}"
            id="{{ $document->getKey() }}"
    >
    </document-switcher>
@endsection