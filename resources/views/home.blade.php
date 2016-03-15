@extends('layouts.app')

@section('title', 'My documents')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">My documents</div>

                <div class="panel-body">
                    <ul>
                        @foreach ($documents as $document)
                            <li><a href="{{ route('document.show', ['id' => $document->getKey()]) }}">{{ $document->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
