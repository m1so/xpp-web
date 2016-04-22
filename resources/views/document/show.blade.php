@extends('layouts.app')

@section('title', $document->title)

@section('content')
    <editor :document="{{ $document->toJson() }}"></editor>
@endsection
