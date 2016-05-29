@extends('layouts.app')

@section('title', 'Changelog')
@section('content-title', 'Changelog')

@section('content')

<div class="col-md-12">
    {{-- Release v1.0.0 --}}
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Release - May 29<sup>th</sup> 2016</h3>
        </div>
        <div class="box-body">
            <h4>v1.0.0</h4>
            <ul>
                <li>Public and private files</li>
                <li>Better document organization</li>
                <li>Graph improvements - 3D freezing, 2D scaling and autoscaling</li>
            </ul>

            <strong>Thesis archive</strong>: <a href="https://is.muni.cz/th/423763/prif_b/?lang=en" target="_blank">Link to Masaryk University</a><br>
            <strong>Issues</strong>: <a href="http://www.github.com/m1so/xpp-web/issues" target="_blank">Open on Github</a>
        </div>
    </div>

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
