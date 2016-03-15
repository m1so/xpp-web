@extends('layouts.app')

@section('title', 'Changelog')
@section('content-title', 'Changelog')

@section('content')

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">v0.0.2 - 10.10.2015</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <ul>
                    <li>Created basic layout with AdminLTE theme</li>
                </ul>
            </div><!-- /.box-body -->
        </div><!-- /.box -->

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">v0.0.1 - 29.6.2015</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <ul>
                    <li>Laravel 5.1 setup</li>
                    <li>Gulp build setup</li>
                </ul>

                Github: <a href="http://www.github.com/m1so/xpp" target="_blank">m1so/xpp</a>
            </div><!-- /.box-body -->
        </div><!-- /.box -->

@endsection