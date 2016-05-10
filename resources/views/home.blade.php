@extends('layouts.app')

@section('title', 'My documents')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Latest documents</div>

                <div class="panel-body">
                    <ul class="todo-list">
                        @foreach ($documents as $document)
                        <li>
                            <a href="{{ route('document.show', ['id' => $document->getKey()]) }}" class="text-black">
                                {{ $document->title }}
                            </a>
                            <span class="pull-right">
                                <i class="fa fa-clock-o"></i> {{ $document->created_at->diffForHumans() }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <documents-list :documents="{{ $documents->toJson() }}"></documents-list>
        </div>
    </div>
</div>
@endsection
