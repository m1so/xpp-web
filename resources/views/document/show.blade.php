@extends('layouts.app')

@section('title', $document->title)
@section('content-title', $document->title)

@section('content')
    <document :data="{{ $document->toJson() }}"></document>
@endsection