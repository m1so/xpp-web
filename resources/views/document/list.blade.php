@extends('layouts.app')

@section('title', 'Documents')
@section('content-title', 'Documents')

@section('content')
    <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header">
                    <h3 class="box-title">List of all my documents</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <ul class="todo-list">
                        @foreach ($documents as $document)
                            <li>
                                <a href="{{ route('document.show', ['id' => $document->getKey()]) }}" class="text-black">{{ $document->title }}</a>
                                <small class="label label-default"><i class="fa fa-clock-o"></i> {{ $document->created_at->diffForHumans() }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix no-border">
                    <button type="button" class="btn btn-default pull-left"><i class="fa fa-plus"></i> Create new document</button>
                </div>
            </div>
    </div>
@endsection