@extends('layouts.app')

@section('title', 'Create from template')
@section('content-title', 'Create a new document from a template')

@section('content')

    <div class="box box-solid">
        <div class="box-body">
            <div class="row">
                <div class="col-xs-4">
                    <label>Search</label>
                    <input type="text" class="form-control" placeholder="Name, content, category, ...">
                </div>
                <div class="col-xs-4">
                    <div class="form-group">
                        <label>Select #1</label>
                        <select class="form-control">
                            <option>option 1</option>
                            <option>option 2</option>
                            <option>option 3</option>
                            <option>option 4</option>
                            <option>option 5</option>
                        </select>
                    </div>
                </div>
                <div class="col-xs-4">
                    <div class="form-group">
                        <label>Select #2</label>
                        <select class="form-control">
                            <option>option 1</option>
                            <option>option 2</option>
                            <option>option 3</option>
                            <option>option 4</option>
                            <option>option 5</option>
                        </select>
                    </div>
                </div>
            </div>
        </div><!-- /.box-body -->
    </div>

    <div class="box box-solid">
        <div class="box-body">
            <!-- RESULTS -->
            <!-- CATEGORY #1 -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h2 class="box-title">Modely růstu a nerovnovážná dynamika</h2>
                </div>
                <div class="box-body">

                    <!-- Templates go here -->
                    <div class="col-md-4">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Pavučinový diagram logistického zobrazení</h3>
                            </div>
                            <div class="box-body">
                                <p>
                                    Pavučinový diagram logistického zobrazení
                                    /el/1431/jaro2015/M6VM06/um/cobweb.ode
                                </p>
                                <button type="button" class="btn btn-default btn-block btn-sm">Use this template</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Bifurkační diagram logistického zobrazení</h3>
                            </div>
                            <div class="box-body">
                                <p>
                                    Bifurkační diagram logistického zobrazení
                                    /el/1431/jaro2015/M6VM06/um/logbif.ode
                                </p>
                                <button type="button" class="btn btn-default btn-block btn-sm">Use this template</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- CATEGORY #2 -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h2 class="box-title">Cesta do více proměnných, lineární modely</h2>
                </div>
                <div class="box-body">

                    <!-- Templates go here -->
                    <div class="col-md-4">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Lineární model zalesnění</h3>
                            </div>
                            <div class="box-body">
                                <p>
                                    Lineární model zalesnění - Xppaut
                                    /el/1431/jaro2015/M6VM06/um/lesy.ode
                                </p>
                                <button type="button" class="btn btn-default btn-block btn-sm">Use this template</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- CATEGORY #3 -->
            <div class="box box-success">
                <div class="box-header with-border">
                    <h2 class="box-title">Strukturované modely, nelineární dynamika</h2>
                </div>
                <div class="box-body">

                    <!-- Templates go here -->
                    <div class="col-md-4">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Spojitý dynamický Cournotův model duopolu (adaptivní hráči)</h3>
                            </div>
                            <div class="box-body">
                                <p>
                                    Spojitý dynamický Cournotův model duopolu (adaptivní hráči)
                                    /el/1431/jaro2015/M6VM06/um/cournot.ode
                                </p>
                                <button type="button" class="btn btn-default btn-block btn-sm">Use this template</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Spojitý dynamický Cournotův model duopolu (omezeně racionální hráči)</h3>
                            </div>
                            <div class="box-body">
                                <p>
                                    Spojitý dynamický Cournotův model duopolu (omezeně racionální hráči)
                                    /el/1431/jaro2015/M6VM06/um/cournot2.ode
                                </p>
                                <button type="button" class="btn btn-default btn-block btn-sm">Use this template</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Diskrétní dynamický Cournotův model duopolu (adaptivní hráči)</h3>
                            </div>
                            <div class="box-body">
                                <p>
                                    Diskrétní dynamický Cournotův model duopolu (adaptivní hráči)
                                    /el/1431/jaro2015/M6VM06/um/cournotdislin.ode
                                </p>
                                <button type="button" class="btn btn-default btn-block btn-sm">Use this template</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Diskrétní dynamický Cournotův model duopolu (omezeně racionální hráči)</h3>
                            </div>
                            <div class="box-body">
                                <p>
                                    Diskrétní dynamický Cournotův model duopolu (omezeně racionální hráči)
                                    /el/1431/jaro2015/M6VM06/um/cournotdis.ode
                                </p>
                                <button type="button" class="btn btn-default btn-block btn-sm">Use this template</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="box box-solid box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Diskrétní dynamický Cournotův model duopolu (omezeně racionální hráči) 2</h3>
                            </div>
                            <div class="box-body">
                                <p>
                                    Diskrétní dynamický Cournotův model duopolu (omezeně racionální hráči) 2
                                    /el/1431/jaro2015/M6VM06/um/cournotdis2.ode
                                </p>
                                <button type="button" class="btn btn-default btn-block btn-sm">Use this template</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- END RESULTS -->

        </div>
    </div>

@endsection