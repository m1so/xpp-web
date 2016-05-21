@extends('layouts.app')

@section('title', 'Public documents')
@section('content-title', 'Public documents')

@section('content')
    <public-documents :documents="{{ $documents->toJson() }}"></public-documents>
@endsection
