@extends('layouts.app')

@section('title', 'Documents')
@section('content-title', 'Documents')

@section('content')
    <documents-list :documents="{{ $documents->toJson() }}"></documents-list>
@endsection
