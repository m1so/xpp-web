@extends('layouts.app')

@section('title', 'Changelog')
@section('content-title', 'Changelog')

@section('content')

<div class="col-md-12">
    {{-- Beta release v0.1.0 --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Beta release - May 12<sup>th</sup> 2016</h3>
        </div>
        <div class="box-body">
            <h4>v0.1.0</h4>
            <ul>
                <li>Interactive ODE editor</li>
                <li>Plotting - 2D, 3D, nullclines, direction fields</li>
                <li>Click for intial conditions on 2D plot</li>
                <li>Document organization and folders</li>
            </ul>

            <strong>Source</strong>: <a href="http://www.github.com/m1so/xpp-web" target="_blank">m1so/xpp-web</a>
        </div>
    </div>
</div>

@endsection
